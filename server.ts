import express from "express";
import { createServer as createViteServer } from "vite";
import path from "path";
import { fileURLToPath } from "url";
import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";
import jwt from "jsonwebtoken";
import dotenv from "dotenv";

dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const prisma = new PrismaClient();
const JWT_SECRET = process.env.JWT_SECRET || "super-secret-laundry-key";

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(express.json());

  // --- API ROUTES ---

  // Auth Middleware
  const authenticateToken = (req: any, res: any, next: any) => {
    const authHeader = req.headers["authorization"];
    const token = authHeader && authHeader.split(" ")[1];

    if (!token) return res.status(401).json({ error: "Access denied" });

    jwt.verify(token, JWT_SECRET, (err: any, user: any) => {
      if (err) return res.status(403).json({ error: "Token invalid" });
      req.user = user;
      next();
    });
  };

  // Auth Routes
  app.post("/api/auth/register", async (req, res) => {
    try {
      const { nama, email, no_telp, alamat, username, password } = req.body;
      const hashedPassword = await bcrypt.hash(password, 10);
      const user = await prisma.user.create({
        data: {
          nama,
          email,
          no_telp,
          alamat,
          username,
          password: hashedPassword,
          role: "USER",
        },
      });
      res.json({ message: "Registration successful", userId: user.id_user });
    } catch (error: any) {
      res.status(400).json({ error: error.message });
    }
  });

  app.post("/api/auth/login", async (req, res) => {
    try {
      const { identifier, password } = req.body; // email or username
      const user = await prisma.user.findFirst({
        where: {
          OR: [{ email: identifier }, { username: identifier }],
        },
      });

      if (!user) return res.status(401).json({ error: "Invalid credentials" });

      const validPassword = await bcrypt.compare(password, user.password);
      if (!validPassword) return res.status(401).json({ error: "Invalid credentials" });

      const token = jwt.sign({ userId: user.id_user, role: user.role }, JWT_SECRET);
      res.json({ token, user: { id: user.id_user, nama: user.nama, role: user.role } });
    } catch (error: any) {
      res.status(500).json({ error: error.message });
    }
  });

  // Services
  app.get("/api/layanan", async (req, res) => {
    const layanan = await prisma.layanan.findMany();
    res.json(layanan);
  });

  // Tracking
  app.get("/api/tracking/:kode", async (req, res) => {
    try {
      const { kode } = req.params;
      const order = await prisma.order.findUnique({
        where: { kode_order: kode },
        include: { tracking: { orderBy: { waktu_update: "desc" } }, kurir: true, layanan: true },
      });
      if (!order) return res.status(404).json({ error: "Order not found" });
      res.json(order);
    } catch (error: any) {
      res.status(500).json({ error: error.message });
    }
  });

  // Admin/Kurir Status update
  app.patch("/api/orders/:id/status", authenticateToken, async (req: any, res) => {
    try {
      const { id } = req.params;
      const { status_order, keterangan } = req.body;
      const order = await prisma.order.update({
        where: { id_order: parseInt(id) },
        data: { status_order },
      });

      await prisma.tracking.create({
        data: {
          id_order: parseInt(id),
          status_tracking: status_order,
          keterangan,
        },
      });

      res.json(order);
    } catch (error: any) {
      res.status(500).json({ error: error.message });
    }
  });
  // Orders
  app.get("/api/orders", authenticateToken, async (req: any, res) => {
    try {
      const { userId, role } = req.user;
      if (role === "ADMIN") {
        const orders = await prisma.order.findMany({ include: { user: true, layanan: true } });
        return res.json(orders);
      }
      const orders = await prisma.order.findMany({
        where: { id_user: userId },
        include: { layanan: true },
      });
      res.json(orders);
    } catch (error: any) {
      res.status(500).json({ error: error.message });
    }
  });

  app.post("/api/orders", authenticateToken, async (req: any, res) => {
    try {
      const { id_layanan, alamat_pickup, alamat_delivery, tanggal_pickup, jam_pickup, catatan, metode_pembayaran } = req.body;
      const kode_order = "ORD-" + Math.random().toString(36).substring(7).toUpperCase();
      
      const order = await prisma.order.create({
        data: {
          id_user: req.user.userId,
          id_layanan,
          kode_order,
          alamat_pickup,
          alamat_delivery,
          tanggal_pickup: new Date(tanggal_pickup),
          jam_pickup,
          catatan,
          status_order: "menunggu pickup",
        },
      });

      // Create initial payment record
      await prisma.pembayaran.create({
        data: {
          id_order: order.id_order,
          metode_pembayaran: metode_pembayaran || "BAYAR DI TEMPAT",
          jumlah: 0, // Will be updated after weight is confirmed
          status_pembayaran: "belum bayar",
        }
      });

      await prisma.tracking.create({
        data: {
          id_order: order.id_order,
          status_tracking: "Order Dibuat",
          keterangan: "Pesanan Anda telah berhasil dibuat.",
        },
      });

      res.json(order);
    } catch (error: any) {
      res.status(500).json({ error: error.message });
    }
  });

  // --- VITE MIDDLEWARE ---

  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    app.use(express.static(path.join(__dirname, "dist")));
    app.get("*", (req, res) => {
      res.sendFile(path.join(__dirname, "dist", "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
