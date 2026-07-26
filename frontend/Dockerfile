FROM node:20-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY . .

EXPOSE 5173

ENV NODE_ENV=development

CMD ["sh", "-c", "npm install --include=dev && node node_modules/vite/bin/vite.js --host 0.0.0.0"]
