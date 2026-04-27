FROM node:20-alpine

WORKDIR /app

# Comente (adicione # na frente) ou remova estas linhas temporariamente:
# COPY package*.json ./
# RUN npm install

# Mantenha apenas o COPY . . e o CMD
COPY . .

EXPOSE 3000

# CMD ["npm", "run", "dev", "--", "--host"]
CMD ["tail", "-f", "/dev/null"]