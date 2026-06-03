import "dotenv/config";
import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  // Clear existing data
  await prisma.layanan.deleteMany({});
  await prisma.user.deleteMany({});

  const hashedPassword = await bcrypt.hash("admin123", 10);

  // Admin
  await prisma.user.create({
    data: {
      nama: "Administrator",
      email: "admin@laundryku.com",
      no_telp: "08123456789",
      alamat: "Pusat LaundryKu",
      username: "admin",
      password: hashedPassword,
      role: "ADMIN",
    },
  });

  // Kurir
  const hashedKurirPassword = await bcrypt.hash("kurir123", 10);
  await prisma.user.create({
    data: {
      nama: "Driver Laundry",
      email: "kurir@laundryku.com",
      no_telp: "081222333444",
      alamat: "Jl. Pengiriman No. 1",
      username: "kurir",
      password: hashedKurirPassword,
      role: "KURIR",
    },
  });

  // Regular User
  const hashedUserPassword = await bcrypt.hash("user123", 10);
  await prisma.user.create({
    data: {
      nama: "Budi Pelanggan",
      email: "budi@gmail.com",
      no_telp: "08555666777",
      alamat: "Perumahan Elite Blok A",
      username: "budi",
      password: hashedUserPassword,
      role: "USER",
    },
  });

  // Services
  const services = [
    {
      nama_layanan: "Laundry Reguler",
      deskripsi: "Cuci lipat rapi, pengerjaan 2-3 hari.",
      harga_per_kg: 6000,
      estimasi_hari: 3,
    },
    {
      nama_layanan: "Laundry Express",
      deskripsi: "Cuci lipat rapi, pengerjaan 1 hari.",
      harga_per_kg: 10000,
      estimasi_hari: 1,
    },
    {
      nama_layanan: "Cuci Setrika",
      deskripsi: "Cuci dan setrika rapi, pengerjaan 2-3 hari.",
      harga_per_kg: 8000,
      estimasi_hari: 3,
    },
    {
      nama_layanan: "Setrika Saja",
      deskripsi: "Setrika rapi tanpa cuci.",
      harga_per_kg: 4000,
      estimasi_hari: 2,
    },
    {
      nama_layanan: "Laundry Sepatu",
      deskripsi: "Pembersihan khusus untuk berbagai jenis sepatu.",
      harga_per_kg: 25000,
      estimasi_hari: 3,
    },
    {
      nama_layanan: "Laundry Karpet",
      deskripsi: "Cuci karpet bersih dan wangi.",
      harga_per_kg: 15000,
      estimasi_hari: 5,
    },
  ];

  for (const service of services) {
    await prisma.layanan.create({
      data: service,
    });
  }

  console.log("Seeding completed!");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
