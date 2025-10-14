# Tender Management System - User Roles Setup Guide

## 🎯 **Role-Based Workflow:**

### **UKK (Unit Kewangan Kerajaan)**
- **Permission**: Create tenders only
- **Workflow**: Initiates tender creation process

### **JPSD (Jabatan Perkhidmatan Sistem Digital)**  
- **Permission**: Update/Edit tenders created by UKK
- **Workflow**: Reviews and updates tender details

### **PANEL (Approval Panel)**
- **Permission**: Approve and publish tenders
- **Workflow**: Final approval and publication

---

## 📋 **Manual Setup Instructions:**

### **Step 1: Create Roles**
1. Go to: `http://localhost:8080/admin/people/roles`
2. Click "Add role" and create these roles:

#### **UKK Role:**
- **Role name**: `ukk`
- **Role label**: `UKK (Unit Kewangan Kerajaan)`

#### **JPSD Role:**
- **Role name**: `jpsd` 
- **Role label**: `JPSD (Jabatan Perkhidmatan Sistem Digital)`

#### **PANEL Role:**
- **Role name**: `panel`
- **Role label**: `PANEL (Approval Panel)`

### **Step 2: Set Permissions**
Go to: `http://localhost:8080/admin/people/permissions`

#### **UKK Permissions:**
- ✅ Access tender management
- ✅ Create tenders
- ✅ View tenders
- ✅ View tender details

#### **JPSD Permissions:**
- ✅ Access tender management
- ✅ View tenders
- ✅ View tender details
- ✅ Edit tenders
- ✅ Update tenders

#### **PANEL Permissions:**
- ✅ Access tender management
- ✅ View tenders
- ✅ View tender details
- ✅ Evaluate tenders
- ✅ Approve tenders
- ✅ Publish tenders
- ✅ Manage tender results

### **Step 3: Create Users**
Go to: `http://localhost:8080/admin/people/create`

#### **UKK Users:**
1. **User 1:**
   - Username: `ukk_user1`
   - Email: `ukk1@credence.com`
   - Password: `password123`
   - Role: UKK
   - Display Name: Ahmad UKK

2. **User 2:**
   - Username: `ukk_user2`
   - Email: `ukk2@credence.com`
   - Password: `password123`
   - Role: UKK
   - Display Name: Siti UKK

#### **JPSD Users:**
1. **User 1:**
   - Username: `jpsd_user1`
   - Email: `jpsd1@credence.com`
   - Password: `password123`
   - Role: JPSD
   - Display Name: Ali JPSD

2. **User 2:**
   - Username: `jpsd_user2`
   - Email: `jpsd2@credence.com`
   - Password: `password123`
   - Role: JPSD
   - Display Name: Fatimah JPSD

#### **PANEL Users:**
1. **User 1:**
   - Username: `panel_user1`
   - Email: `panel1@credence.com`
   - Password: `password123`
   - Role: PANEL
   - Display Name: Dato' Panel

2. **User 2:**
   - Username: `panel_user2`
   - Email: `panel2@credence.com`
   - Password: `password123`
   - Role: PANEL
   - Display Name: Datin Panel

---

## 🔄 **Workflow Process:**

1. **UKK User** logs in → Creates new tender → Saves as draft
2. **JPSD User** logs in → Reviews UKK's tender → Updates/edits details → Marks ready for approval
3. **PANEL User** logs in → Reviews JPSD's updates → Approves → Publishes tender (Active state)

---

## 🌐 **Quick Access Links:**

- **User Management**: `http://localhost:8080/admin/people`
- **Roles**: `http://localhost:8080/admin/people/roles`
- **Permissions**: `http://localhost:8080/admin/people/permissions`
- **Create User**: `http://localhost:8080/admin/people/create`
- **Tender Dashboard**: `http://localhost:8080/tender-management`

---

## ✅ **Test Workflow:**

1. Login as UKK user → Create a tender
2. Login as JPSD user → Edit the tender
3. Login as PANEL user → Approve and publish

This setup ensures proper role segregation and workflow control in your tender management system!