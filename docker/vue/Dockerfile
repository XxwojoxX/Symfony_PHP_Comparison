FROM node:18

WORKDIR /app
COPY ../frontend-vue/package.json ./
RUN npm install
COPY . ./

CMD ["npm", "run", "dev"]
EXPOSE 5173
