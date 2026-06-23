# Measure Objects
This project is divided into two main components: a Computer Vision Engine and a Web Application. The primary goal of this project is to detect and measure objects from images using the YOLOv8 model and OpenCV.
---
## 📖 Project Description
This is a full-stack application:
1. **Vision Engine (Backend):** Built using Python and FastAPI. It utilizes the YOLOv8 Segmentation model (`yolov8n-seg.pt`) to extract objects from the image and calculate their measurements.
2. **Web App (Frontend):** Built using Laravel (PHP) and Vite. It provides the user interface where users can upload photos and view the results by connecting to the vision engine.
### 🛠 Tech Stack
- **Backend:** Python, FastAPI, YOLOv8, OpenCV
- **Frontend:** PHP, Laravel, Node.js, Vite
---
## 🚀 How to Run the Project
Follow these steps to run the project locally on your machine.
### 1. Running the Vision Engine (FastAPI Backend)
First, start the FastAPI backend server. Open your terminal and run the following commands:
```bash
# Navigate to the vision-engine folder
cd vision-engine
# Activate the virtual environment
# For Windows:
.\venv\Scripts\activate
# For Mac/Linux:
source venv/bin/activate
# Start the server on port 8000
python -m uvicorn main:app --reload --port 8000
API Access URL: http://localhost:8000

2. Running the Web App (Laravel Frontend)
Open a new terminal window to start the frontend application.

bash


# Navigate to the web-app folder
cd web-app
# Install dependencies if running for the first time
composer install
npm install
npm run dev
# Start the Laravel server on port 8001
php artisan serve --port 8001
Web App Access URL: http://localhost:8001



I have also saved this directly to your [README.md](file:///f:/AQS/measure-objects/README.md) file. You can now commit and push it to Git!
