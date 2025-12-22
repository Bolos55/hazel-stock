@echo off
echo Starting Tailwind CSS watch mode...
echo Press Ctrl+C to stop
npx tailwindcss -i ./src/input.css -o ./css/tailwind.css --watch