#!/usr/bin/env bash
# Simple environment check for Quote Dashboard

if command -v node >/dev/null 2>&1 && command -v npm >/dev/null 2>&1; then
  echo "Node and npm are installed"
else
  echo "Node or npm not found. Install Node.js from https://nodejs.org/"
fi

# Placeholder for additional setup steps (e.g., composer or npm install)
# None required at this time
