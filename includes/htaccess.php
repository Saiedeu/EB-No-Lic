# Deny all access to includes directory
Order Deny,Allow
Deny from all

# Additional protection
<Files "*">
    Order Allow,Deny
    Deny from all
</Files>