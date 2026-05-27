import requests
import base64
import time
import os

# Use an existing image from the database
DB_PATH = os.path.join(os.path.dirname(os.path.dirname(__file__)), "uploads", "facial")
images = [f for f in os.listdir(DB_PATH) if f.endswith(".jpeg") or f.endswith(".jpg")]

if not images:
    print("No images found in uploads/facial")
    exit()

test_image = os.path.join(DB_PATH, images[0])
print(f"Testing with: {test_image}")

with open(test_image, "rb") as f:
    img_b64 = base64.b64encode(f.read()).decode()

url = "http://127.0.0.1:5000/analyze"
payload = {"image_data": img_b64}

print("Starting benchmark...")
for i in range(3):
    start = time.time()
    try:
        res = requests.post(url, json=payload, timeout=30)
        duration = time.time() - start
        print(f"Request {i+1}: {duration:.2f}s - Response: {res.json()}")
    except Exception as e:
        print(f"Error: {e}")
