# Changelog - RetroLoved

## [Unreleased] - 2024-12-15

### Added
- **Sequential Image Upload** - New one-by-one image upload system for products
  - Grid layout 4x2 with 8 slots
  - Sequential activation after each upload
  - Individual delete buttons
  - Visual preview and status feedback

- **Close Button** - X icon in product-add page header (top-right corner)

- **Conditional Submit Button** - Submit button only shows in final tab (Status & Pengaturan)

- **Cleanup Scripts**
  - `cleanup.bat` - Remove unnecessary files
  - `reset_database.sql` - Reset database (preserve admin)
  - `push_to_github.bat` - Automated GitHub push

- **Documentation**
  - `CLEANUP_INSTRUCTIONS.md` - Cleanup guide
  - `GITHUB_PUSH_GUIDE.md` - GitHub push guide
  - `COMMIT_MESSAGE.txt` - Detailed commit message
  - `CHANGELOG.md` - This file

### Fixed
- **Delete Address Bug** - Address deletion now works in customer profile
- **Icon Alignment Issues**
  - Fixed + icon in "Tambah Alamat" buttons (checkout.php)
  - Fixed truck icon alignment (order-tracking.php)
  - All icons now properly aligned with text

- **Database Reset Issues**
  - Fixed foreign key constraint errors
  - Proper deletion order for related tables
  - Used DELETE instead of TRUNCATE for FK tables

### Changed
- **Twitter to X Logo** - Updated social media icon in footer
- **Button Styling** - Improved consistency across all buttons
- **.gitignore** - Added temp_retroloved/ exclusion

### Technical
- Proper FK constraint handling in database operations
- Improved CSS for icon alignment (flex, line-height, display: block)
- Better error handling in cleanup scripts

---

## Summary of Changes

**Total Files Modified:** 6
**Total Files Added:** 7
**Lines Changed:** ~500+

**Key Improvements:**
1. ✅ Better UX for product image upload
2. ✅ Fixed critical delete address bug
3. ✅ Improved visual consistency
4. ✅ Added cleanup and maintenance tools
5. ✅ Better documentation

**Testing Status:** All changes tested and verified ✅

---

## Migration Notes

If updating from previous version:
1. Backup database before running reset_database.sql
2. Run cleanup.bat to remove old files
3. Update .gitignore if needed
4. Test admin login after database reset

---

## Contributors
- Development & Bug Fixes
- UI/UX Improvements
- Documentation

---

## Next Steps
- [ ] Add more product categories
- [ ] Implement product search
- [ ] Add customer reviews
- [ ] Improve mobile responsiveness
- [ ] Add analytics dashboard
