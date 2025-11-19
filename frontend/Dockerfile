FROM node:20-alpine

WORKDIR /app

# Copia package.json e lock para instalar dependências
COPY package*.json ./

RUN npm install

# Copiar resto do código
COPY . .

# Expor porta padrão do Vite
EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
