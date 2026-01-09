# Release Notes

## [v1.2.0] - 2024-12-XX

### Improvements
- **Refactored SEO Entries Blade Template**: Streamlined search and filter section, updated table structure, and ensured proper indentation and formatting throughout the file for improved readability and consistency
- **Volt Components Integration**: Refactored SEO management route definitions to utilize Volt components for improved organization and clarity, aligning with recent updates in the project structure
- **Enhanced Dark Mode Support**: Improved UI consistency across SEO management views by updating styles and classes in Blade templates. Adjusted button styles, form containers, and text colors to ensure better visibility and accessibility in dark mode
- **Improved Route Organization**: Refactored route definitions in README.md and Installer.php for SEO management to use a more organized structure with prefix and name grouping, enhancing clarity for developers
- **Better Navigation Structure**: Updated README.md to rename SEO Management to SEO Entries, and added separate menu items for Media SEO and Global Settings for improved clarity and organization in the admin panel navigation
- **Enhanced User Experience**: Refactored SEO management views to utilize Volt components, implement validation attributes, and enhance user notifications for actions such as saving, activating, and deleting entries. Updated form structures for better usability and consistency across the admin panel
- **Documentation Updates**: Refactored README.md to clarify installation steps, update route definitions to use Volt components, and enhance uninstallation command documentation

### Technical Changes
- Updated route definitions to use Volt components instead of traditional Livewire components
- Improved form validation attributes across all SEO management views
- Enhanced error handling and user feedback mechanisms
- Better code organization and consistency in Blade templates

---

## [v1.1.0] - Previous Release

### Features
- Initial stable release with core SEO management functionality
- Page-level and media-level SEO support
- Snapshot-based SEO resolution
- Admin panel integration with Livewire
- Global SEO settings management
- Placeholder support for dynamic SEO content
- Cache-first performance optimization

---

## [v1.0.0] - Initial Release

### Features
- Initial release of ATU Rank SEO package
- Basic SEO management functionality
- Integration with Vormia SlugRegistry
- Admin panel UI components
- Database migrations and seeders
- Installation and uninstallation commands
