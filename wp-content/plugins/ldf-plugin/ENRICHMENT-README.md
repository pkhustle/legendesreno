# Professional AI Enrichment Feature

## Overview

This feature enriches professional posts with data from Google Places API, Site Reviews, and Hostinger AI to automatically populate:

- ✅ **City** - Extracted from Google Places structured address
- ✅ **Featured Image** - First photo from Google Places
- ✅ **Photo Gallery** - Up to 5 business photos from Google Places
- ✅ **Review Ratings** - Averages from Site Reviews custom criteria
- ✅ **AI-Generated Description** - Professional business description using Hostinger AI

---

## Setup Instructions

### 1. Add Google Places API Key

Open `wp-config.php` and add this line **before** the `/* That's all, stop editing! */` comment:

```php
define('GOOGLE_PLACES_API_KEY', 'your-google-api-key-here');
```

**How to get a Google Places API key:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable these APIs:
   - Places API (New)
   - Places API (Legacy) - for photo support
4. Create credentials → API Key
5. Restrict the key to your domain for security

**Required APIs:**
- `Places API` - for searching and details
- `Places API (Legacy)` - for photo downloads

---

## Usage

### Option 1: Single Professional (Meta Box)

1. Go to `Professionals` → `Edit` any professional
2. Look for the **"AI Enrichment"** meta box in the sidebar
3. Click **"Enrich with AI"**
4. Wait for completion (status updates will show)
5. Page will reload with updated data

### Option 2: Bulk Enrichment

1. Go to `Professionals` → `All Professionals`
2. Select one or more professionals using checkboxes
3. Choose **"Enrich with AI"** from the Bulk Actions dropdown
4. Click **Apply**
5. Watch the progress bar as each professional is processed

### Option 3: Row Action

1. Go to `Professionals` → `All Professionals`
2. Hover over any professional row
3. Click the **"Enrich"** quick action link
4. Confirm the dialog
5. Watch the status update inline

---

## What Gets Updated

| Data | Source | Behavior |
|------|--------|----------|
| **Place ID** | Google Text Search | Stored for future lookups |
| **City** | Google address_components | Always updated if found |
| **Featured Image** | Google Places Photos | Only if none exists |
| **Photo Gallery** | Google Places Photos (up to 5) | Only if gallery is empty |
| **Review Ratings** | Site Reviews plugin | Averages calculated from custom fields |
| **Description** | Hostinger AI | Only if post content is empty or < 50 chars |

---

## ACF Field Configuration

The plugin expects these ACF field names (customizable in code):

```php
// In professional-enricher.php, edit these constants if needed:
const ACF_ADDRESS = 'address';           // Full address field
const ACF_CITY = 'city';                 // City field
const ACF_PHOTOS = 'photos';             // Gallery field
const ACF_PLACE_ID = 'place_id';         // Google Place ID storage
const ACF_QUALITY_AVG = 'quality_of_work_avg';
const ACF_TIMELINESS_AVG = 'timeliness_avg';
const ACF_PROFESSIONALISM_AVG = 'professionalism_avg';
const ACF_VALUE_AVG = 'value_for_money_avg';
```

**To customize field names:**
1. Edit `wp-content/plugins/ldf-plugin/professional-enricher.php`
2. Update the constants at the top of the class
3. Save and refresh

---

## Site Reviews Configuration

The plugin expects these custom rating criteria in Site Reviews:

- `quality_of_work`
- `timeliness`
- `professionalism`
- `value_for_money`

**To verify/setup:**
1. Go to `Site Reviews` → `Settings` → `Forms`
2. Edit your review form
3. Add custom fields with the exact names above
4. Make them rating fields (1-5 stars)

---

## Troubleshooting

### "No Google API key" error
- Check that `GOOGLE_PLACES_API_KEY` is defined in `wp-config.php`
- Make sure there are no typos or extra spaces

### "Not found in Google Places" error
- The business name + address didn't match any results
- Try editing the address to be more complete
- Manually add a `place_id` ACF field if you have it

### "Hostinger AI not available" error
- The Hostinger AI Assistant plugin is not active
- Activate it via `Plugins` → `Hostinger AI`

### "No reviews found" error
- No Site Reviews exist for this professional
- Reviews need to be assigned to the professional post

### Photos not downloading
- Check Google API key has Places API enabled
- Check your WordPress upload directory permissions
- Look in `Media Library` to see if they uploaded but failed to attach

### Description not generating
- Post already has content (>50 characters)
- Check Hostinger AI plugin is configured correctly
- Try enriching again - AI APIs can occasionally timeout

---

## API Costs

**Google Places API:**
- Text Search: $32 per 1000 requests
- Place Details: $17 per 1000 requests
- Place Photos: $7 per 1000 requests

**Estimated cost per professional enrichment:**
- Text Search: 1 request = $0.032
- Place Details: 1 request = $0.017
- Photos: 5 requests = $0.035
- **Total: ~$0.084 per professional**

**Hostinger AI:**
- Included with Hostinger hosting
- No additional costs

---

## Technical Details

### Flow Diagram

```
1. Text Search (name + address) → Place ID
2. Place Details (place_id) → city, photos[], reviews[]
3. Download Photos → WP Media Library
4. Extract Site Reviews → Calculate averages
5. Generate Description (Hostinger AI) → post_content
```

### Data Storage

- Place ID stored in ACF field `place_id` (prevents re-searching)
- Photos uploaded to WordPress media library
- Review averages stored in ACF fields
- Description stored as post content

### Performance

- AJAX-based (won't timeout PHP)
- Sequential processing for bulk actions
- Progress tracking UI
- 30-second timeout per photo download

---

## Development

**File Locations:**
- Main plugin: `wp-content/plugins/ldf-plugin/professional-enricher.php`
- Integration: `wp-content/plugins/ldf-plugin/acf-relationship-display.php`
- This readme: `wp-content/plugins/ldf-plugin/ENRICHMENT-README.md`

**Hooks Available:**
```php
// Add custom processing before enrichment
add_action('ldf_before_enrich_professional', function($post_id) {
    // Your code here
});

// Add custom processing after enrichment
add_action('ldf_after_enrich_professional', function($post_id, $result) {
    // Your code here
}, 10, 2);
```

---

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review WordPress debug logs
3. Contact your developer

---

## Version History

**v1.0** (April 2026)
- Initial release
- Google Places integration
- Site Reviews rating extraction
- Hostinger AI description generation
- Bulk action support
- Meta box UI
- Row action shortcuts
