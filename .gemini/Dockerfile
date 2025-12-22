FROM php:8.3-cli

WORKDIR /app

ENV TZ=Europe/Zurich

# Install Node.js + npm
RUN apt-get update && apt-get install -y \
    git \
    curl \
    ca-certificates \
    gnupg \
 && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y nodejs \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Verify
RUN node -v && npm -v

# Install Gemini CLI
RUN npm install -g @google/gemini-cli
