@echo off
echo Building Tailwind CSS...
npx tailwindcss -i ./src/input.css -o ./css/tailwind.css --minify
echo Build complete!
pause