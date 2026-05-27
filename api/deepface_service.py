from fastapi.middleware.cors import CORSMiddleware
import os
import glob
import base64
import numpy as np
import cv2
from fastapi import FastAPI, HTTPException, Body
from deepface import DeepFace
import uvicorn
from typing import Optional
import json
import time

app = FastAPI(title="Sisponto DeepFace Service")

# Configuração de CORS para permitir acesso da Locaweb
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Caminho para o banco de imagens (onde as fotos dos funcionários estão salvas)
DB_PATH = os.path.join(os.path.dirname(os.path.dirname(__file__)), "uploads", "facial")

# Configurações do DeepFace (Facenet512 é mais preciso que VGG-Face)
MODEL_NAME = "Facenet512" 
DETECTOR_BACKEND = "ssd" 
DISTANCE_METRIC = "cosine"

@app.get("/")
async def root():
    return {
        "status": "online",
        "message": "Sisponto DeepFace Service is running",
        "model": MODEL_NAME,
        "detector": DETECTOR_BACKEND
    }

@app.on_event("startup")
async def startup_event():
    """
    Pré-carrega os modelos na memória ao iniciar o serviço para evitar atraso na primeira requisição.
    """
    print(f"[STARTUP] Pré-carregando modelo {MODEL_NAME}...")
    try:
        DeepFace.build_model(MODEL_NAME)
        # Warm up do detector com uma imagem vazia
        DeepFace.represent(
            img_path=np.zeros((640, 640, 3), dtype=np.uint8), 
            model_name=MODEL_NAME, 
            detector_backend=DETECTOR_BACKEND, 
            enforce_detection=False
        )
        print("[STARTUP] Modelos e detectores prontos para uso.")
    except Exception as e:
        print(f"[STARTUP] Aviso: Erro no warm-up (pode ser ignorado): {e}")

@app.post("/analyze")
async def analyze_face(image_data: str = Body(..., embed=True)):
    """
    Recebe uma imagem base64 e tenta encontrar o funcionário correspondente no DB_PATH (1:N).
    """
    start_time = time.time()
    try:
        # Decodificar imagem base64
        if "data:image" in image_data:
            header, image_data = image_data.split(",")
        
        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            raise HTTPException(status_code=400, detail="Imagem inválida")

        # Redimensionar para acelerar detecção (SSD escala com o tamanho da imagem)
        # 640px é o ponto ideal para o SSD ser rápido e ainda detectar bem
        max_dim = 640
        h, w = img.shape[:2]
        if max(h, w) > max_dim:
            scale = max_dim / max(h, w)
            img = cv2.resize(img, (int(w * scale), int(h * scale)))

        # Realizar busca no banco de imagens
        results = DeepFace.find(
            img_path=img,
            db_path=DB_PATH,
            model_name=MODEL_NAME,
            distance_metric=DISTANCE_METRIC,
            detector_backend=DETECTOR_BACKEND,
            enforce_detection=False,
            silent=True
        )

        duration = time.time() - start_time
        print(f"[ANALYZE] Processado em {duration:.2f}s")

        if not results or len(results[0]) == 0:
            duration = time.time() - start_time
            print(f"[ANALYZE] Nenhum match em {duration:.2f}s")
            return {"success": False, "message": "Nenhum match encontrado"}

        # Pegar o melhor resultado (menor distância)
        df = results[0]
        best_match = df.iloc[0]
        identity_path = best_match['identity']
        
        # Tentar encontrar a coluna de distância (DeepFace muda o nome conforme a versão/métrica)
        dist_col = None
        for col in df.columns:
            if col in ['distance', f"{MODEL_NAME}_{DISTANCE_METRIC}", DISTANCE_METRIC]:
                dist_col = col
                break
        
        if dist_col:
            distance = float(best_match[dist_col])
        else:
            # Fallback: pega a última coluna que não seja 'identity'
            distance = float(best_match.iloc[-1])

        # O threshold depende do modelo. Para Facenet512 + Cosine, ~0.30 é o padrão.
        threshold = 0.23 
        
        if distance > threshold:
            duration = time.time() - start_time
            print(f"[ANALYZE] Face reconhecida mas abaixo do limite ({distance:.4f}) em {duration:.2f}s")
            return {"success": False, "message": "Face reconhecida mas abaixo do limite de confiança", "distance": distance}

        # Retornar o caminho da imagem encontrada
        rel_path = os.path.relpath(identity_path, os.path.join(os.path.dirname(os.path.dirname(__file__))))
        rel_path = rel_path.replace("\\", "/").lower() # Forçar minúsculas para match no DB

        duration = time.time() - start_time
        print(f"[ANALYZE] Match OK: {os.path.basename(identity_path)} em {duration:.2f}s (dist: {distance:.4f})")

        return {
            "success": True,
            "identity": rel_path,
            "distance": distance,
            "model": MODEL_NAME
        }

    except Exception as e:
        print(f"Erro no analyze: {str(e)}")
        return {"success": False, "message": f"Erro interno: {str(e)}"}

@app.post("/verify")
async def verify_face(
    image_data: str = Body(..., embed=True),
    stored_path: str = Body(..., embed=True)
):
    """
    Compara uma imagem capturada com um arquivo específico (1:1).
    """
    start_time = time.time()
    try:
        if "data:image" in image_data:
            header, image_data = image_data.split(",")
        
        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        full_stored_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), stored_path)
        
        if not os.path.exists(full_stored_path):
            raise HTTPException(status_code=404, detail="Foto original não encontrada")

        # Redimensionar para 1:1 também
        max_dim = 480
        h, w = img.shape[:2]
        if max(h, w) > max_dim:
            scale = max_dim / max(h, w)
            img = cv2.resize(img, (int(w * scale), int(h * scale)))

        result = DeepFace.verify(
            img1_path=img,
            img2_path=full_stored_path,
            model_name=MODEL_NAME,
            distance_metric=DISTANCE_METRIC,
            detector_backend=DETECTOR_BACKEND,
            enforce_detection=False,
            silent=True
        )

        duration = time.time() - start_time
        print(f"[VERIFY] Processado em {duration:.2f}s")

        return {
            "success": result["verified"],
            "distance": float(result["distance"]),
            "threshold": float(result["threshold"]),
            "model": MODEL_NAME
        }

    except Exception as e:
        print(f"Erro no verify: {str(e)}")
        return {"success": False, "message": f"Erro interno: {str(e)}"}

@app.post("/cleanup")
async def cleanup_faces(valid_filenames: list = Body(..., embed=True)):
    """
    Recebe a lista de nomes de arquivo válidos (vindo do banco de dados PHP).
    Remove do DB_PATH todos os arquivos que não estão na lista e invalida o cache.
    """
    try:
        removed = []
        kept = []
        valid_set = {os.path.basename(f).lower() for f in valid_filenames}

        for filepath in glob.glob(os.path.join(DB_PATH, "*.*")):
            fname = os.path.basename(filepath).lower()
            if fname.endswith(".pkl"):
                os.remove(filepath)  # sempre remove cache
                continue
            if fname not in valid_set:
                os.remove(filepath)
                removed.append(fname)
            else:
                kept.append(fname)

        print(f"[CLEANUP] Mantidos: {len(kept)} | Removidos: {len(removed)}")
        return {"success": True, "kept": len(kept), "removed": removed}

    except Exception as e:
        print(f"[CLEANUP] Erro: {str(e)}")
        return {"success": False, "message": f"Erro: {str(e)}"}


@app.post("/save_face")
async def save_face(
    image_data: str = Body(..., embed=True),
    filename: str = Body(..., embed=True)
):
    """
    Recebe uma imagem base64, salva em DB_PATH e invalida o cache do DeepFace.find().
    Chamado pelo PHP sempre que um funcionário cadastra ou atualiza sua foto facial.
    """
    try:
        if "data:image" in image_data:
            _, image_data = image_data.split(",", 1)

        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return {"success": False, "message": "Imagem inválida"}

        os.makedirs(DB_PATH, exist_ok=True)

        save_path = os.path.join(DB_PATH, os.path.basename(filename))
        cv2.imwrite(save_path, img)

        # Invalida o índice do DeepFace.find() para forçar re-indexação
        for pkl in glob.glob(os.path.join(DB_PATH, "*.pkl")):
            os.remove(pkl)

        print(f"[SAVE_FACE] Salvo: {save_path}")
        return {"success": True, "filename": os.path.basename(filename)}

    except Exception as e:
        print(f"[SAVE_FACE] Erro: {str(e)}")
        return {"success": False, "message": f"Erro: {str(e)}"}


if __name__ == "__main__":
    # Rodar o serviço na porta 5000
    print(f"Iniciando serviço DeepFace no DB_PATH: {DB_PATH}")
    uvicorn.run(app, host="0.0.0.0", port=5000)
