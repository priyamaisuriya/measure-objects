# measure-objects
# Measure Objects

This project is divided into two main components: a Computer Vision Engine and a Web Application. The primary goal of this project is to detect and measure objects from images using the YOLOv8 model and OpenCV.

## Project Description

This is a full-stack application:
1. **Vision Engine (Backend):** This part is built using Python and FastAPI. It utilizes the YOLOv8 Segmentation model (`yolov8n-seg.pt`) to extract objects from the image and calculate their measurements.
2. **Web App (Frontend):** This part is built using Laravel (PHP). It provides the user interface where users can upload photos and view the results by connecting to the vision engine.

### 🛠 Tech Stack
- **Backend:** Python, FastAPI, YOLOv8, OpenCV
- **Frontend:** PHP, Laravel, Node.js, Vite

---

## 🚀 How to Run the Project

Follow these steps to run the project locally:

### 1. Running the Vision Engine (FastAPI Backend)

First, open your terminal and navigate to the `vision-engine` directory:
```bash
cd vision-engine
