قالب كمبيوتيك WordPress Theme

طريقة التركيب:
1) ارفع ملف computech-wp-theme.zip من: Appearance > Themes > Add New > Upload Theme.
2) فعّل القالب.
3) أنشئ الصفحات التالية واضبط الـ slug كما يلي:
   - الرئيسية: اختَرها كـ Homepage من Settings > Reading.
   - about
   - services
   - categories
   - products
   - contact
   - product-details  (صفحة تحويل/تنبيه فقط؛ تفاصيل المنتجات الحقيقية من رابط المنتج)
4) من كل صفحة اختَر القالب المناسب من Page Attributes > Template إذا لم يتم التقاطه تلقائياً.
5) المنتجات الجديدة تُدار من لوحة التحكم من: منتجات كمبيوتيك.
6) بعد التفعيل ادخل Settings > Permalinks واضغط Save Changes مرة واحدة.

ملاحظات:
- لا توجد منتجات أو أقسام hard-coded في واجهات المنتجات/الأقسام.
- صفحة products تعرض فقط منتجات الداتابيز من post type: products.
- سكشن تسوق حسب القسم في الرئيسية يعرض فقط أقسام product_category التي عليها Show in Shop Section = true، وبترتيب Shop Section Order.
- سكشن منتجات مميزة يعرض فقط المنتجات التي عليها Show in Featured Products = true، وبترتيب Featured Products Order.
- صفحة أقسام المتجر تعرض الأقسام من taxonomy product_category فقط، وقسم الأقسام المميزة يعتمد على Show in Featured Categories.
- تفاصيل المنتج الحقيقية تكون على رابط /product/اسم-المنتج/.
- صفحة product-details لم تعد تعرض بيانات JavaScript قديمة؛ لو وصلها product_id أو slug صالح ستحوّل لرابط المنتج الحقيقي.

Navigation fix v2:
- The theme now auto-creates required WordPress pages if they do not exist.
- Navigation links now use actual WordPress page permalinks instead of assuming fixed pretty URLs.
- After uploading this version, visit Dashboard once, then go to Settings > Permalinks and click Save Changes if any page still shows 404.

Update: Home page responsive hardening layer added. The home page sections now use flexible auto-fit grids and fluid typography so adding/removing cards, icons, products, payment items, social links, or text should not break the layout.

ARCHITECTURE UPDATE — Categories & Products
-------------------------------------------
This version adds a dedicated architecture layer at:
inc/computech-architecture.php

Implemented:
- Product Category remains an unlimited-depth hierarchical taxonomy.
- Product can be assigned to any category depth.
- Product category pages include products assigned to the current category and all descendant categories.
- Product has a Primary Category used for breadcrumbs.
- Category dashboard fields:
  - Visibility
  - Order inside same parent
  - Category Image from Media Library
  - Category Icon
  - Show in Shop Section
  - Shop Section Order
  - Shop Badge Text
  - Shop Button Text
  - Show in Featured Categories
  - Featured Categories Order
  - Featured Badge Text
  - Featured Button Text
- Product dashboard fields:
  - Brand, Model, SKU, Visibility
  - Product Gallery
  - Primary Category
  - Regular/Sale Price, Currency, Show Price, Price Note
  - Condition, Availability, Badge Text, Stock Quantity
  - Card Title Override, Card Subtitle, Highlights, Card Note
  - Show in Featured Products, Featured Products Order
  - Flexible specs
  - Warranty & Support
  - Buttons / Actions
- Hidden products are removed from archive/category/search/home featured products.
- Hidden categories are removed from home sections/category lists.
- Products page filter can match parent categories through product ancestor category slugs.


DYNAMIC DATA CLEANUP — No hard-coded categories/products
-------------------------------------------------------
Applied in this version:
- Removed the static products fallback template usage from page-products.php.
- Cleared template-parts/static/products-content.php from old hard-coded product cards.
- Removed JavaScript product details sample array from assets/js/main.js.
- Disabled automatic seeding of sample featured products.
- Disabled automatic seeding/restoring of old home category card CPT data.
- Disabled the old home category card CPT registration so the dashboard source of truth is Product Categories taxonomy.
- Category archive breadcrumbs now start with: الرئيسية / أقسام المتجر / category path.
- Product breadcrumbs use Primary Category path under أقسام المتجر when a primary category exists.
- Taxonomy category pages query products with include_children=true, so parent categories show products assigned to descendants.

PRODUCT DASHBOARD EDITOR FIX
----------------------------
Applied in this version:
- Products now use the Classic WordPress edit screen, not Gutenberg/block editor.
- Reason: the product architecture fields are WordPress meta boxes. Gutenberg was collapsing them into a bottom "Meta Boxes" drawer, which made the product fields look missing.
- The product edit screen now directly shows:
  - Pricing
  - Product gallery
  - Primary category
  - Visibility
  - Featured products settings
  - Card display fields
  - Specs
  - Warranty/support
  - Buttons/actions
- Adding, editing, deleting products and assigning categories is still done from Dashboard > منتجات كمبيوتيك.

MAIN HEADER NAVIGATION UPDATE
-----------------------------
Applied in this version:
- The main header navigation no longer reads links from the custom Computech settings page.
- The main header navigation now reads only from the native WordPress menu location: primary / القائمة الرئيسية.
- To edit header links, go to Appearance > Menus, create or edit a menu, then assign it to: القائمة الرئيسية.
- Menu item add/edit/delete/order is now controlled by WordPress Nav Menus and saved in the WordPress database.
- The same WordPress menu is used for desktop and mobile navigation.
- The custom Computech settings page still controls top bar, logo, search, cart, and WhatsApp only.
