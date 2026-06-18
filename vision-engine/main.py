import cv2
import numpy as np
from fastapi import FastAPI, UploadFile, File, WebSocket, WebSocketDisconnect
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

# Load the YOLOv8 Segmentation model
model = YOLO("yolov8n-seg.pt")

# Default calibration: pixels per cm
DEFAULT_PIXELS_PER_CM = 35.0  

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
        
        # Create a completely white background image
        white_frame = np.ones_like(frame) * 255
        
        # Find the main object (largest area that is NOT a person or chair)
        main_box = None
        main_mask = None
        main_class_name = None
        max_area = 0
        
        for result in results:
            if result.boxes is None or result.masks is None:
                continue

            for box, mask_xy in zip(result.boxes, result.masks.xy):
                class_id = int(box.cls[0].item())
                class_name = model.names[class_id]
                
                # Ignore people, chairs, and background furniture
                if class_name in ['person', 'chair', 'couch', 'bed', 'tv', 'laptop']:
                    continue
                    
                x1_val, y1_val, x2_val, y2_val = box.xyxy[0].cpu().numpy()
                area = (x2_val - x1_val) * (y2_val - y1_val)
                
                if area > max_area:
                    max_area = area
                    main_box = box
                    main_mask = mask_xy
                    main_class_name = class_name
                    
        # If we found a main object, process it
        if main_box is not None:
            x1_val, y1_val, x2_val, y2_val = main_box.xyxy[0].cpu().numpy()
            x1, y1, x2, y2 = int(x1_val), int(y1_val), int(x2_val), int(y2_val)
            
            pixel_width = x2_val - x1_val
            pixel_height = y2_val - y1_val
            
            real_width = round(float(pixel_width) / DEFAULT_PIXELS_PER_CM, 2)
            real_height = round(float(pixel_height) / DEFAULT_PIXELS_PER_CM, 2)
            
            measurements.append({
                "type": "target_object",
                "class": main_class_name,
                "width_cm": real_width,
                "height_cm": real_height
            })
            
            # Pixel Perfect Cutout
            pts = np.array(main_mask, dtype=np.int32)
            
            # Create a black mask of the same size as the frame
            obj_mask = np.zeros(frame.shape[:2], dtype=np.uint8)
            
            # Fill the object's polygon with white (255)
            cv2.fillPoly(obj_mask, [pts], 255)
            
            # Copy ONLY the object's pixels from the original frame to the white frame
            white_frame[obj_mask == 255] = frame[obj_mask == 255]
            
            # Draw Bounding Box
            cv2.rectangle(white_frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            
            # Label
            label = f"{main_class_name.upper()}: {real_width}cm x {real_height}cm"
            (text_width, text_height), baseline = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)
            cv2.rectangle(white_frame, (x1, y1 - text_height - 10), (x1 + text_width, y1), (0, 255, 0), -1)
            cv2.putText(white_frame, label, (x1, y1 - 5), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 0, 0), 2)

        if len(measurements) == 0:
            return {"status": "error", "message": "No objects found in the image!"}

        # Convert the drawn image to Base64 to send back to the frontend
        _, buffer = cv2.imencode('.jpg', white_frame)
        base64_image = base64.b64encode(buffer).decode('utf-8')

        return {
            "status": "success", 
            "measurements": measurements,
            "image_base64": base64_image
        }

    except Exception as e:
        return {"status": "error", "message": str(e)}

from pydantic import BaseModel

class LiveFrameRequest(BaseModel):
    image_base64: str

@app.post("/live")
async def live_stream(request: LiveFrameRequest):
    try:
        data = request.image_base64
        if "," in data:
            data = data.split(",")[1]
            
        image_data = base64.b64decode(data)
        nparr = np.frombuffer(image_data, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if frame is None:
            return {"status": "error", "message": "Failed to decode image"}
            
        results = model(frame, conf=0.15)
        white_frame = np.ones_like(frame) * 255
        
        main_mask = None
        max_area = 0
        
        for result in results:
            if result.boxes is None or result.masks is None:
                continue
            for box, mask_xy in zip(result.boxes, result.masks.xy):
                class_id = int(box.cls[0].item())
                class_name = model.names[class_id]
                if class_name in ['person', 'chair', 'couch', 'bed', 'tv', 'laptop']:
                    continue
                
                x1_val, y1_val, x2_val, y2_val = box.xyxy[0].cpu().numpy()
                area = (x2_val - x1_val) * (y2_val - y1_val)
                if area > max_area:
                    max_area = area
                    main_mask = mask_xy
        
        if main_mask is not None:
            pts = np.array(main_mask, dtype=np.int32)
            obj_mask = np.zeros(frame.shape[:2], dtype=np.uint8)
            cv2.fillPoly(obj_mask, [pts], 255)
            white_frame[obj_mask == 255] = frame[obj_mask == 255]
        
        _, buffer = cv2.imencode('.jpg', white_frame, [int(cv2.IMWRITE_JPEG_QUALITY), 50])
        out_base64 = base64.b64encode(buffer).decode('utf-8')
        
        return {"status": "success", "image_base64": out_base64}
        
    except Exception as e:
        return {"status": "error"}

@app.get("/")
def read_root():
    return {"message": "Vision Engine API is running"}
