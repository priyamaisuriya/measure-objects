import cv2
import numpy as np
from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
import traceback
import base64
from ultralytics import YOLO

app = FastAPI(title="Object Measurement API")

# Allow CORS for the frontend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Load the YOLOv8 model (it will download yolov8n.pt automatically the first time)
model = YOLO('yolov8n.pt')

# Fallback ratio if no coin is present (just for demonstration so it doesn't fail)
DEFAULT_PIXELS_PER_CM = 25.0

@app.post("/measure")
async def measure_objects(image: UploadFile = File(...)):
    try:
        contents = await image.read()
        nparr = np.frombuffer(contents, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if frame is None:
             return {"status": "error", "message": "Failed to decode image."}

        # Run YOLO object detection with lower confidence threshold
        results = model(frame, conf=0.15)
        
        measurements = []
        
        for result in results:
            boxes = result.boxes
            for box in boxes:
                # Get class ID and name
                class_id = int(box.cls[0].item())
                class_name = model.names[class_id]
                
                # Thermos bottles are sometimes misclassified as cups or vases by YOLOv8n
                if class_name not in ['bottle', 'cup', 'vase']:
                    continue

                # Get coordinates
                x1, y1, x2, y2 = box.xyxy[0].cpu().numpy()
                
                # Calculate pixel dimensions
                pixel_width = x2 - x1
                pixel_height = y2 - y1
                
                # Calculate real dimensions using our default ratio
                real_width = round(float(pixel_width) / DEFAULT_PIXELS_PER_CM, 2)
                real_height = round(float(pixel_height) / DEFAULT_PIXELS_PER_CM, 2)
                
                measurements.append({
                    "type": "target_object",
                    "class": class_name,
                    "width_cm": real_width,
                    "height_cm": real_height
                })
                
                # Draw on the image
                # 1. Bounding Box
                cv2.rectangle(frame, (int(x1), int(y1)), (int(x2), int(y2)), (0, 255, 0), 2)
                
                # 2. Label and Dimensions
                label = f"{class_name.upper()}: {real_width}cm x {real_height}cm"
                
                # Add background for text to make it readable
                (text_width, text_height), baseline = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)
                cv2.rectangle(frame, (int(x1), int(y1) - text_height - 10), (int(x1) + text_width, int(y1)), (0, 255, 0), -1)
                
                # Put the text
                cv2.putText(frame, label, (int(x1), int(y1) - 5), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 0, 0), 2)

        if len(measurements) == 0:
            return {"status": "error", "message": "No bottle found in the image!"}

        # Convert the drawn image to Base64 to send back to the frontend
        _, buffer = cv2.imencode('.jpg', frame)
        base64_image = base64.b64encode(buffer).decode('utf-8')

        return {
            "status": "success", 
            "measurements": measurements,
            "image_base64": base64_image
        }

    except Exception as e:
        return {"status": "error", "message": str(e), "traceback": traceback.format_exc()}

@app.get("/")
def read_root():
    return {"message": "Vision Engine is running!"}
