#!/usr/bin/env bash

set -e

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"

cleanup() {
  echo ""
  echo "Shutting down all services..."
  kill 0
  wait
  echo "All services stopped."
}

trap cleanup SIGINT SIGTERM

# Backend (artisan serve, reverb, queue, vite)
echo "Starting backend..."
(cd "$ROOT_DIR/backend" && composer dev) &
BACKEND_PID=$!

# Frontend (vite)
echo "Starting frontend (port 5173)..."
(cd "$ROOT_DIR/frontend" && npm run dev) &
FRONTEND_PID=$!

# Admin frontend (vite)
echo "Starting admin-frontend (port 3001)..."
(cd "$ROOT_DIR/admin-frontend" && npm run dev) &
ADMIN_PID=$!

echo ""
echo "All services started:"
echo "  Backend:          http://localhost:8000"
echo "  Reverb:           ws://localhost:8080"
echo "  Frontend:         http://localhost:5173"
echo "  Admin Frontend:   http://localhost:3001"
echo ""
echo "Press Ctrl+C to stop all services."
echo ""

wait $BACKEND_PID $FRONTEND_PID $ADMIN_PID
