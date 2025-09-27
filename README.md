# NRD Form Builder

> A lightweight, secure, and powerful drag-and-drop form builder for WordPress.  
> Create unlimited forms, manage submissions, get email notifications, and send data directly into **Google Sheets** — without shipping Google credentials inside the plugin.

---

## 📌 Table of Contents
- [Features](#-features)
- [License Activation](#-license-activation)
- [Creating a Form](#-creating-a-form)
- [Google Sheets Integration](#-google-sheets-integration)
- [How It Works (Security)](#-how-it-works-security)
- [Email Notifications](#-email-notifications)
- [Submissions Dashboard](#-submissions-dashboard)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Support](#-support)
- [Changelog](#-changelog)

---

## ✨ Features

- Drag-and-drop form builder (powered by jQuery FormBuilder)  
- Unlimited forms & submissions  
- File uploads with strict validation  
- Email notifications for every submission  
- Submissions dashboard inside WordPress  
- Google Sheets integration (per-form linkage)  
- Secure architecture — no Google credentials inside the plugin  
- Built-in license system for premium updates  

---

## 🔑 License Activation

1. After installing the plugin, go to **NRD Form BD → Dashboard**  
2. Enter your license key (provided after purchase)  
3. Click **Activate**  
4. Once activated, premium features are unlocked  

---

## 📝 Creating a Form

1. In the WordPress admin, navigate to **NRD BD Forms → Add New**  
2. Use the drag-and-drop editor to build your form  
3. Save the form  
4. Copy the shortcode shown in the editor:

   ```php
   [nrd_form_bd id="123"]
5. Paste it into any page or post  
6. Publish the page — your form is now live and ready to collect submissions  

---

## 📊 Google Sheets Integration

Each form can be linked to its own Google Sheet:

1. Edit your form (CPT: **NRD BD Form**)  
2. In the **Google Sheet ID** box:  
   - Enter the **Spreadsheet ID** (the long string in the Sheet’s URL)  
   - Enter the **Sheet Page Name** (tab name inside the sheet)  
3. Save the form  
4. Share your Google Sheet with the provided service account email:  
nrd-form-builder@your-service-project.iam.gserviceaccount.com
Give it **Editor** access  

From now on, all submissions for this form will sync to that sheet  

---

## 🔒 How It Works (Security)

- The plugin **does not bundle Google credentials**  
- Instead, it sends form data to the **NRD API Bridge** hosted securely by us  
- The bridge authenticates with Google using our service account  
- Your data is appended to the linked Google Sheet  

**Benefits:**  
- Lightweight plugin (no heavy `vendor/google/apiclient` dependency)  
- Credentials are never exposed in distributed code  

---

## 📬 Email Notifications

- Submissions are emailed to the WordPress admin email by default  
- You can override this per form using the **Notify Email** field  
- Emails include all submitted fields in a structured HTML table  

---

## 📥 Submissions Dashboard

- Submissions are saved in WordPress as a CPT: `nrd-form-bd-submit`  
- Access them under **NRD Form BD → Submissions**  
- Each submission shows:  
- Parent form link  
- All submitted fields (table + JSON)  
- Quick preview (e.g., name/email if provided)  
- File upload URLs  

---

## 🛠️ Requirements

- WordPress 6.0+  
- PHP 7.4+  
- HTTPS-enabled site (required for Google API calls)  

---

## 🚀 Installation

1. Download the latest release from [Releases](../../releases)  
2. Upload the `.zip` via **Plugins → Add New → Upload**  
3. Activate the plugin  
4. Go to **NRD Form BD → Dashboard** to enter your license key  
5. Create your first form under **NRD BD Forms**  

---

## 🤝 Support

- 📖 Documentation: [https://nrdformbuilder.com/docs](#)  
- 📧 Email: [support@nrdformbuilder.com](mailto:support@nrdformbuilder.com)  
- 🐞 Issues: [GitHub Issues](../../issues)  

---

## 📌 Changelog

### 1.0.0
- Initial release with drag-and-drop builder, submissions CPT, email notifications, and Google Sheets integration via secure API bridge  
