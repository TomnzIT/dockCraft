# 🎮 dockCraft 🌐

A project combining a Dockerized Minecraft server and a web interface for managing the server via RCON.

## ℹ️ Description

This project includes a Dockerized Minecraft server using the [itzg/minecraft-server](https://github.com/itzg/docker-minecraft-server) container and a PHP web interface for sending commands to the server via RCON. You can easily run and manage your own Minecraft server using the user-friendly web interface.

## 🚀 How to Use

1. Make sure you have Docker installed on your machine: `docker --version`.
2. Clone this repository: `git clone https://github.com/TomnzIT/dockCraft.git`.
3. Navigate to the project directory: `cd dockCraft`.
4. Run the Docker containers using Docker Compose: `docker-compose up -d`.
5. Open your browser and go to: `http://localhost` to access the RCON web interface for Minecraft.

## 🛠️ Configuration

- Make sure to update the RCON connection information in the `docker-compose.yml` and `index.php` files.
- The `docker-compose.yml` file configures the Docker services to run the Minecraft server and the web server with Apache and PHP.
- The default Minecraft server port is `25565`, the default RCON port is `25575` and the web interface port is `80`.
- You can modify the server properties by adding variables to environment in the `docker-compose.yml`.
