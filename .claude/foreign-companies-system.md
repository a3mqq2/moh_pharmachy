# نظام الشركات الأجنبية - Foreign Companies System

## نظرة عامة
نظام تسجيل الشركات الأجنبية يسمح لممثل الشركة الذي لديه شركة محلية من نوع "مورد" بتسجيل شركات أجنبية (شركات أو مصانع).

---

## قاعدة البيانات

### 1. جدول الشركات الأجنبية (foreign_companies)

| الحقل | النوع | الوصف |
|------|------|-------|
| id | bigint | المعرف الأساسي |
| representative_id | foreign key | ربط مع ممثل الشركة |
| local_company_id | foreign key | ربط مع الشركة المحلية (الوكيل) |
| company_name | string | اسم الشركة الأجنبية |
| country | string | البلد |
| entity_type | enum | نوع الكيان (company/factory) |
| address | text | عنوان الشركة |
| email | string | البريد الإلكتروني |
| activity_type | enum | نوع النشاط (medicines/medical_supplies/both) |
| products_count | integer | عدد المنتجات المراد تسجيلها |
| registered_countries | json | قائمة الدول المسجلة بها |
| status | enum | الحالة |
| rejection_reason | text | سبب الرفض |
| approved_at | timestamp | تاريخ الموافقة |
| activated_at | timestamp | تاريخ التفعيل |
| approved_by | foreign key | الموظف الذي وافق |

**حالات الشركة:**
- `uploading_documents` - قيد رفع المستندات
- `pending` - قيد المراجعة
- `pending_payment` - قيد السداد
- `approved` - مقبولة
- `active` - مفعلة
- `rejected` - مرفوضة
- `suspended` - معلقة

---

### 2. جدول مستندات الشركات الأجنبية (foreign_company_documents)

| الحقل | النوع | الوصف |
|------|------|-------|
| id | bigint | المعرف الأساسي |
| foreign_company_id | foreign key | ربط مع الشركة الأجنبية |
| document_type | enum | نوع المستند |
| document_name | string | اسم المستند |
| file_path | string | مسار الملف |
| file_size | string | حجم الملف |
| mime_type | string | نوع الملف |
| notes | text | ملاحظات |
| status | enum | حالة المستند (pending/approved/rejected) |
| rejection_reason | text | سبب الرفض |
| reviewed_by | foreign key | الموظف المراجع |
| reviewed_at | timestamp | تاريخ المراجعة |

**أنواع المستندات المطلوبة:**
1. `official_registration_request` - طلب تسجيل رسمي من الشركة المصنعة
2. `agency_agreement` - رسالة وكالة أو اتفاقية توزيع مع الشركة المحلية
3. `registration_forms` - نماذج التسجيل المعتمدة (ورقي + إلكتروني)
4. `gmp_certificate` - شهادة GMP سارية
5. `fda_certificate` - شهادة FDA
6. `emea_certificate` - شهادة EMEA (بديل لـ FDA)
7. `cpp_certificate` - شهادة المنتج الصيدلاني (CPP)
8. `fsc_certificate` - شهادة البيع الحر (FSC) - بديل لـ CPP
9. `manufacturing_license` - ترخيص تصنيع ساري
10. `financial_report` - تقرير مالي لآخر سنتين
11. `products_list` - قائمة منتجات الشركة (5 منتجات على الأقل)
12. `site_master_file` - الملف الرئيسي للمصنع
13. `registration_certificates` - شهادات تسجيل في دول أخرى
14. `exclusive_agency_contract` - عقد الوكالة الحصري
15. `other` - مستندات أخرى

**ملاحظات إلزامية:**
- جميع الشهادات يجب تصديقها من السلطات الصحية في بلد المنشأ
- جميع الشهادات يجب تصديقها من السفارة الليبية
- المستندات بلغة أجنبية يجب ترجمتها ترجمة قانونية
- يجب تقديم جميع المستندات عبر شركة استيراد محلية مسجلة

---

### 3. جدول فواتير الشركات الأجنبية (foreign_company_invoices)

| الحقل | النوع | الوصف |
|------|------|-------|
| id | bigint | المعرف الأساسي |
| foreign_company_id | foreign key | ربط مع الشركة الأجنبية |
| invoice_number | string unique | رقم الفاتورة |
| amount | decimal(10,2) | المبلغ |
| description | text | الوصف |
| status | enum | حالة الفاتورة (pending/paid/cancelled) |
| receipt_path | string | مسار الإيصال |
| receipt_uploaded_at | timestamp | تاريخ رفع الإيصال |
| receipt_status | enum | حالة الإيصال (pending/approved/rejected) |
| receipt_rejection_reason | text | سبب رفض الإيصال |
| receipt_reviewed_by | foreign key | الموظف المراجع للإيصال |
| receipt_reviewed_at | timestamp | تاريخ مراجعة الإيصال |
| paid_at | timestamp | تاريخ الدفع |
| issued_by | foreign key | الموظف الذي أصدر الفاتورة |
| approved_by | foreign key | الموظف الذي وافق على الدفع |

---

## سير العمل (Workflow)

### 1. مرحلة التسجيل
```
1. ممثل الشركة يسجل شركة أجنبية (بيانات أساسية)
2. النظام يتحقق من أن لديه شركة محلية من نوع "مورد"
3. حالة الشركة: uploading_documents
```

### 2. مرحلة رفع المستندات
```
1. ممثل الشركة يرفع المستندات المطلوبة
2. يمكن رفع المستندات بشكل تدريجي
3. عند إكمال جميع المستندات المطلوبة، يمكن إرسال الطلب للمراجعة
4. حالة الشركة: pending
```

### 3. مرحلة المراجعة (من قبل الإدارة)
```
1. الموظف يراجع البيانات والمستندات
2. الموظف يمكنه:
   - قبول المستند
   - رفض المستند مع توضيح السبب

إذا تم رفض مستند:
   - حالة الشركة: uploading_documents
   - ممثل الشركة يستلم إشعار
   - يجب رفع المستند المرفوض مرة أخرى

إذا تم قبول جميع المستندات:
   - حالة الشركة: approved
   - يتم إصدار فاتورة تلقائياً
   - حالة الشركة: pending_payment
   - إرسال إيميل للممثل
```

### 4. مرحلة الدفع
```
1. ممثل الشركة يدخل لصفحة الفواتير
2. يرفع إيصال الدفع
3. حالة الشركة تبقى: pending_payment
4. الموظف يراجع الإيصال:
   - إذا قُبل: حالة الشركة → active
   - إذا رُفض: حالة الشركة → rejected (مؤقتاً)
     - ممثل الشركة يرفع إيصال جديد
```

---

## الصفحات المطلوبة

### A. صفحات ممثل الشركة (Representative)

#### 1. صفحة قائمة الشركات الأجنبية
- المسار: `/representative/foreign-companies`
- العرض: جدول/بطاقات بجميع الشركات الأجنبية المسجلة
- الأزرار:
  - "تسجيل شركة أجنبية جديدة" (يظهر فقط إذا كان لديه شركة محلية من نوع مورد)
  - "عرض التفاصيل" لكل شركة

#### 2. صفحة تسجيل شركة أجنبية جديدة
- المسار: `/representative/foreign-companies/create`
- الحقول:
  - اسم الشركة
  - البلد (قائمة منسدلة)
  - نوع الكيان (شركة / مصنع)
  - اختيار الشركة المحلية (قائمة بالشركات المحلية من نوع مورد)
  - عنوان الشركة
  - البريد الإلكتروني
  - نوع النشاط (أدوية / مستلزمات طبية / كلاهما)
  - عدد المنتجات المراد تسجيلها
  - قائمة الدول المسجلة بها (multi-select)

#### 3. صفحة تفاصيل الشركة الأجنبية
- المسار: `/representative/foreign-companies/{id}`
- التبويبات:
  1. **البيانات الأساسية**: عرض جميع معلومات الشركة
  2. **المستندات**:
     - قائمة بجميع أنواع المستندات المطلوبة
     - حالة كل مستند (pending/approved/rejected)
     - إمكانية رفع المستندات
     - إمكانية حذف/استبدال المستندات (إذا كانت الحالة uploading_documents)
  3. **الفواتير والمدفوعات**:
     - قائمة الفواتير
     - رفع إيصالات الدفع
     - حالة كل فاتورة
  4. **النشاطات**: سجل بجميع التغييرات والعمليات

### B. صفحات الإدارة (Admin)

#### 1. صفحة قائمة الشركات الأجنبية
- المسار: `/admin/foreign-companies`
- العرض: جدول بجميع الشركات مع فلترة حسب:
  - الحالة
  - البلد
  - نوع النشاط
  - الشركة المحلية
- الأزرار:
  - "مراجعة" لكل شركة

#### 2. صفحة مراجعة الشركة الأجنبية
- المسار: `/admin/foreign-companies/{id}`
- التبويبات:
  1. **البيانات الأساسية**: عرض معلومات الشركة
  2. **المستندات**:
     - عرض جميع المستندات
     - إمكانية تحميل المستندات
     - أزرار "قبول" و "رفض" لكل مستند
     - حقل سبب الرفض
  3. **الفواتير**:
     - إصدار فاتورة جديدة
     - مراجعة الإيصالات
     - قبول/رفض الإيصالات
  4. **النشاطات**: سجل العمليات

#### 3. إجراءات الموافقة
عند الموافقة على جميع المستندات:
- تغيير حالة الشركة إلى `approved`
- إصدار فاتورة تلقائياً
- تغيير الحالة إلى `pending_payment`
- إرسال إيميل إلى ممثل الشركة

عند الموافقة على الإيصال:
- تغيير حالة الشركة إلى `active`
- إرسال إيميل تفعيل

---

## Models المطلوب إنشاؤها

### 1. ForeignCompany Model
```php
- Relations:
  - belongsTo(CompanyRepresentative)
  - belongsTo(LocalCompany)
  - hasMany(ForeignCompanyDocument)
  - hasMany(ForeignCompanyInvoice)
  - hasMany(LocalCompanyActivity) // for tracking

- Scopes:
  - scopeActive()
  - scopePending()
  - scopeApproved()

- Accessors:
  - getEntityTypeNameAttribute()
  - getActivityTypeNameAttribute()
  - getStatusNameAttribute()

- Methods:
  - markAsApproved()
  - markAsActive()
  - markAsRejected($reason)
  - canUploadDocuments()
  - hasAllRequiredDocuments()
```

### 2. ForeignCompanyDocument Model
```php
- Relations:
  - belongsTo(ForeignCompany)
  - belongsTo(User, 'reviewed_by')

- Methods:
  - approve()
  - reject($reason)
  - getDocumentTypeNameAttribute()
```

### 3. ForeignCompanyInvoice Model
```php
- Relations:
  - belongsTo(ForeignCompany)
  - belongsTo(User, 'issued_by')
  - belongsTo(User, 'approved_by')
  - belongsTo(User, 'receipt_reviewed_by')

- Methods:
  - hasReceipt()
  - approveReceipt()
  - rejectReceipt($reason)
  - markAsPaid()
```

---

## Controllers المطلوب إنشاؤها

### 1. Representative/ForeignCompanyController
```php
- index(): عرض قائمة الشركات
- create(): عرض نموذج التسجيل
- store(): حفظ الشركة الجديدة
- show(): عرض تفاصيل الشركة
- edit(): تعديل البيانات (إذا كانت في حالة uploading_documents)
- update(): حفظ التعديلات
```

### 2. Representative/ForeignCompanyDocumentController
```php
- store(): رفع مستند جديد
- download(): تحميل مستند
- destroy(): حذف مستند
```

### 3. Representative/ForeignCompanyInvoiceController
```php
- index(): قائمة الفواتير
- show(): تفاصيل فاتورة
- uploadReceipt(): رفع إيصال دفع
- downloadReceipt(): تحميل الإيصال
- deleteReceipt(): حذف الإيصال
```

### 4. Admin/ForeignCompanyController
```php
- index(): قائمة جميع الشركات مع الفلترة
- show(): مراجعة الشركة
- approve(): الموافقة على الشركة
- reject(): رفض الشركة
```

### 5. Admin/ForeignCompanyDocumentController
```php
- approve(): الموافقة على مستند
- reject(): رفض مستند
- download(): تحميل مستند
```

### 6. Admin/ForeignCompanyInvoiceController
```php
- store(): إصدار فاتورة جديدة
- show(): تفاصيل الفاتورة
- approveReceipt(): الموافقة على إيصال
- rejectReceipt(): رفض إيصال
```

---

## Emails المطلوبة

1. **ForeignCompanyApprovedMail**: عند الموافقة على الشركة وإصدار الفاتورة
2. **ForeignCompanyActivatedMail**: عند تفعيل الشركة بعد الدفع
3. **ForeignCompanyDocumentRejectedMail**: عند رفض مستند
4. **ForeignCompanyReceiptRejectedMail**: عند رفض إيصال الدفع

---

## الخطوات التالية

### المرحلة 1: إكمال Models
- [ ] تعبئة ForeignCompany Model
- [ ] تعبئة ForeignCompanyDocument Model
- [ ] تعبئة ForeignCompanyInvoice Model

### المرحلة 2: إنشاء Controllers
- [ ] Representative/ForeignCompanyController
- [ ] Representative/ForeignCompanyDocumentController
- [ ] Representative/ForeignCompanyInvoiceController
- [ ] Admin/ForeignCompanyController
- [ ] Admin/ForeignCompanyDocumentController
- [ ] Admin/ForeignCompanyInvoiceController

### المرحلة 3: إنشاء Views
- [ ] Representative views (index, create, show)
- [ ] Admin views (index, show/review)

### المرحلة 4: Routes والتكامل
- [ ] إضافة routes
- [ ] إضافة middleware للتحقق من الصلاحيات
- [ ] تحديث قائمة التنقل

### المرحلة 5: Emails والإشعارات
- [ ] إنشاء templates الإيميلات
- [ ] إضافة النشاطات (Activities)

---

## ملاحظات تقنية

### التحقق من الصلاحيات
- ممثل الشركة يجب أن يكون لديه شركة محلية من نوع "مورد" لتسجيل شركات أجنبية
- ممثل الشركة يمكنه فقط رؤية شركاته الأجنبية
- الموظفون فقط يمكنهم مراجعة واعتماد الشركات

### تخزين الملفات
- المستندات تُخزن في: `storage/app/public/foreign_companies/{company_id}/documents/`
- الإيصالات تُخزن في: `storage/app/public/foreign_companies/{company_id}/receipts/`

### الأمان
- جميع العمليات يجب أن تتحقق من الصلاحيات
- التحقق من نوع الملفات المرفوعة
- التحقق من حجم الملفات (max 10MB)

---

## تم إنجازه حتى الآن ✅

1. ✅ إنشاء جدول foreign_companies مع جميع الحقول المطلوبة
2. ✅ إنشاء جدول foreign_company_documents مع 15 نوع مستند
3. ✅ إنشاء جدول foreign_company_invoices
4. ✅ تشغيل migrations
5. ✅ إنشاء Models الأساسية (بدون محتوى بعد)

---

## المطلوب التالي 🚀

1. تعبئة Models بالعلاقات والدوال
2. إنشاء Controllers
3. إنشاء Views
4. إضافة Routes
5. إنشاء Emails

هل تريد أن نبدأ بتعبئة Models أو الانتقال مباشرة للـ Controllers والـ Views؟
