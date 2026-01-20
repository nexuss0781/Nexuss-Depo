Here is a professional, **Industry Grade `README.md`** file. You can include this in your project repository or deliver it alongside the files.

It covers installation, usage, folder structure, and crucial security warnings specific to your shared hosting environment.

***

```markdown
# 🚀 Infinity PHP Deployer

A lightweight, self-hosted deployment dashboard designed for Shared Hosting environments (specifically optimized for **InfinityFree**). 

This tool allows you to deploy multiple PHP applications (Telegram bots, simple websites, APIs) to a single hosting account by uploading a `.zip` file. It automatically handles directory creation, extraction, and project organization.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg) ![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

*   **⚡ Zero Configuration:** No database required for the deployer itself.
*   **📂 Drag & Drop Interface:** Modern UI built with Tailwind CSS.
*   **📦 Automatic Unzipping:** Upload a `.zip` and it auto-deploys to a dedicated subdirectory.
*   **🔍 Live Search:** Instantly filter through dozens of deployed projects.
*   **🤖 Telegram Friendly:** Generates Webhook URLs automatically upon successful deployment.
*   **🛡️ Atomic Deployment:** Automatically cleans up if extraction fails to prevent corrupt folders.

---

## 📂 Directory Structure

Your server file structure will look like this after installation:

```text
htdocs/
├── deployments/          # (Created automatically) All your apps live here
│   ├── weather-bot/      # Example App 1
│   │   └── index.php
│   └── portfolio-site/   # Example App 2
│       └── index.php
├── index.php             # The Dashboard Interface
├── deploy.php            # The Backend Logic
└── README.md
```

---

## 🛠️ Installation

1.  **Download Source:** Download `index.php` and `deploy.php`.
2.  **Upload:** Log in to your InfinityFree File Manager (or use FileZilla) and upload both files to the root `htdocs/` folder.
3.  **Permissions:** Ensure the `htdocs` folder has write permissions (usually `755` by default).

That's it. Navigate to `http://your-site.rf.gd/` to see the dashboard.

---

## 📖 Usage Guide

### 1. Preparing your Application
**⚠️ CRITICAL:** How you zip your files matters.

*   **❌ Wrong:** Zipping the *folder* containing your code.
    *   *Result:* `deployments/my-app/my-app-folder/index.php` (Broken paths)
*   **✅ Right:** Select all files **inside** your project folder, Right-Click -> **"Send to Compressed (zipped) folder"**.
    *   *Result:* `deployments/my-app/index.php` (Correct)

### 2. Deploying
1.  Open the Dashboard (`index.php`).
2.  Enter a **Project Name** (e.g., `telegram-bot-v1`).
    *   *Allowed characters: Letters, numbers, and dashes only.*
3.  Drag and drop your `.zip` file.
4.  Click **Deploy to Production**.

### 3. Telegram Webhooks
If deploying a bot:
1.  On the "Success" screen, copy the generated **App URL**.
2.  Click the "Set Webhook" helper link provided on the success page.
3.  Paste your Bot Token into the URL in the browser address bar.

---

## ⚠️ Limitations & Troubleshooting (InfinityFree)

### 1. File Upload Limits
PHP on shared hosting usually has a max upload size (often 10MB - 64MB).
*   **Fix:** If your `.zip` is larger than the limit, upload it manually via FTP to `deployments/your-folder/` and unzip it there.

### 2. Inode Limit (File Count)
InfinityFree has a strict limit of roughly **30,000 files** per account.
*   **Advice:** Avoid deploying heavy frameworks (like Laravel/Composer projects with massive `vendor` folders) unless you strip unnecessary dev-dependencies. This tool is best for lightweight scripts and bots.

### 3. "404 Not Found"
*   Check that your uploaded app has an `index.php` file at the root of the zip.
*   Ensure you didn't zip a folder inside a folder (see "Preparing your Application").

---

## 🔒 Security Warning

**This tool is intended for personal use or internal team use.**

Since InfinityFree does not offer easy password protection for specific files:
1.  **Obscure the Filenames:** Consider renaming `index.php` to something unique like `dashboard_8x9s.php` so the public cannot guess the URL.
2.  **Add Authentication:** Ideally, add a simple PHP session password check to the top of `index.php` and `deploy.php` if you intend to share the URL.
3.  **Shared Environment:** Remember that **App A** can technically read/delete files from **App B** because they share the same hosting user. Do not host competing client sites on the same account.

---
