# Manual Testing Checklist

## Overview
This checklist provides comprehensive manual testing steps for all UI/UX improvements implemented in the Church Management System.

---

## 🔐 Authentication Pages

### Login Page (`/login`)
- [ ] **Password Visibility Toggle**
  - Click eye icon next to password field
  - Verify password text becomes visible (type="text")
  - Click again to hide password (type="password")
  - Icon changes appropriately (eye → eye-slash)

- [ ] **Email Validation**
  - Enter invalid email (e.g., "test@")
  - Verify error message appears immediately
  - Enter valid email (e.g., "test@example.com")
  - Verify success indicator appears

- [ ] **Form Submission**
  - Submit with empty fields → Verify validation errors
  - Submit with invalid email → Verify error message
  - Submit with valid credentials → Verify redirect to dashboard
  - Submit with wrong password → Verify error message

- [ ] **Accessibility**
  - Tab through all fields in order
  - Verify focus indicators are visible
  - Screen reader announces field labels correctly
  - Error messages are announced by screen reader

- [ ] **Responsive Design**
  - Test on mobile (320px width)
  - Test on tablet (768px width)
  - Test on desktop (1920px width)
  - No horizontal scrolling on any viewport

### Register Page (`/register`)
- [ ] **Password Strength Indicator**
  - Type weak password ("pass") → Red bar, "Weak" label
  - Type medium password ("Passw0rd") → Yellow bar, "Medium" label
  - Type strong password ("P@ssw0rd123!") → Green bar, "Strong" label
  - Verify 4-level strength bars update in real-time

- [ ] **Password Match Validation**
  - Enter different passwords in password and confirm fields
  - Verify error message appears on confirm field
  - Make passwords match → Verify success indicator

- [ ] **Email Validation**
  - Same validation as login page

- [ ] **Form Help Text**
  - Verify password requirements are displayed
  - Verify all required fields are marked with asterisk

- [ ] **Navigation Links**
  - "Already have an account? Login" link works
  - Clicking returns to login page

---

## 📊 Dashboard (`/dashboard`)

- [ ] **Personalized Greeting**
  - Visit in morning (before 12 PM) → "Good morning"
  - Visit in afternoon (12-6 PM) → "Good afternoon"
  - Visit in evening (after 6 PM) → "Good evening"
  - User's name is displayed in greeting

- [ ] **Quick Actions Panel**
  - Panel is visible with gradient background
  - "Add Member" button navigates to member creation
  - "Add Donation" button navigates to donation creation
  - "Create Event" button navigates to event creation
  - All icons are visible and properly aligned

- [ ] **Stat Cards**
  - Total Members card shows correct count
  - Total Donations card shows correct amount
  - Upcoming Events card shows correct count
  - Each card is clickable and navigates to respective page
  - Hover effects work on all cards
  - Icons are visible and colored appropriately

- [ ] **Module Cards**
  - Members section shows summary info
  - Donations section shows summary info
  - Events section shows summary info
  - "View all" links navigate correctly
  - Cards adapt to mobile layout (stack vertically)

- [ ] **Loading States**
  - Refresh page → Verify skeleton loaders appear
  - Wait for data → Verify smooth transition to content
  - No layout shift during loading

---

## 👥 Members Management (`/members`)

- [ ] **Search Functionality**
  - Type in search box → Results filter in real-time
  - Search by name → Correct members shown
  - Search by email → Correct members shown
  - Clear search → All members return
  - Debounce works (no excessive API calls)

- [ ] **Status Filters**
  - Click "All" → Shows all members
  - Click "Active" → Shows only active members
  - Click "Inactive" → Shows only inactive members
  - Filter state persists during navigation
  - Active filter is visually highlighted

- [ ] **Sorting**
  - Click "Name" column header → Sort alphabetically
  - Click again → Reverse sort
  - Click "Email" column header → Sort by email
  - Click "Status" → Sort by status
  - Click "Joined Date" → Sort by date
  - Sort direction indicator (arrow) shows correctly

- [ ] **Member Avatars**
  - Each member shows circular avatar
  - Avatar displays first two initials of name
  - Initials are uppercase
  - Background color varies by member
  - Avatar loads before other content

- [ ] **Status Badges**
  - Active members show green badge
  - Inactive members show gray/red badge
  - Badge text is clear ("Active"/"Inactive")
  - Badge is readable on mobile

- [ ] **Pagination**
  - Navigate to next page → Correct members shown
  - Navigate to previous page → Works correctly
  - Jump to specific page number → Works
  - Page numbers update correctly
  - "Showing X of Y entries" text is accurate

- [ ] **Empty State**
  - Search for non-existent member → Empty state appears
  - Illustration/icon is visible
  - Helpful message is displayed
  - "Clear filters" button works

- [ ] **Actions**
  - Edit button opens edit form/modal
  - Delete button shows confirmation dialog
  - Confirm delete → Member is removed
  - Cancel delete → Member remains

- [ ] **Responsive Table**
  - On mobile: Table scrolls horizontally OR stacks
  - All columns are accessible on small screens
  - Action buttons remain accessible
  - No content overflow or cut-off text

---

## 📅 Events Management (`/events`)

- [ ] **Date Range Presets**
  - Click "Today" → Shows only today's events
  - Click "This Week" → Shows this week's events
  - Click "This Month" → Shows this month's events
  - Click "Next Month" → Shows next month's events
  - Active preset is highlighted

- [ ] **Custom Date Range**
  - Open date picker
  - Select start date
  - Select end date
  - Apply → Events filtered correctly
  - Cancel → Filters reset

- [ ] **Sort Options**
  - Sort by "Date" → Chronological order
  - Sort by "Name" → Alphabetical order
  - Sort by "Created" → By creation date
  - Direction toggle works

- [ ] **View Toggle**
  - Grid view → Events display as cards
  - List view → Events display as rows
  - Toggle persists during session
  - Both views show same events

- [ ] **Event Cards (Grid View)**
  - Event title is prominent
  - Date and time are clearly displayed
  - Location is shown if available
  - Description preview is truncated properly
  - Status badge is visible
  - Card hover effect works

- [ ] **Event Status Badges**
  - Upcoming events → Blue badge
  - Ongoing events → Green badge
  - Completed events → Gray badge
  - Cancelled events → Red badge
  - Badge text is clear

- [ ] **Event Actions**
  - View button opens event details
  - Edit button opens edit form
  - Delete button shows confirmation
  - Share button works (if implemented)

- [ ] **Empty State**
  - Filter to show no events → Empty state appears
  - Illustration is relevant to events
  - "Create Event" CTA is visible
  - Message is helpful

- [ ] **Responsive Design**
  - Grid adapts from 3 columns to 2 to 1 on mobile
  - List view remains readable on mobile
  - No horizontal scrolling issues

---

## 💰 Donations Page (`/donations`)

- [ ] **Date Range Presets**
  - Today, This Week, This Month, This Quarter, This Year
  - Each preset filters donations correctly
  - Active preset is highlighted
  - Date range display updates

- [ ] **Summary Statistics**
  - Total Count shows correct number
  - Total Amount shows correct sum with currency
  - Average Amount shows correct calculation
  - Stats update when filters change
  - Stats cards are visually distinct

- [ ] **Table Sorting**
  - Sort by Donor name
  - Sort by Amount (numeric sort)
  - Sort by Date
  - Sort by Category
  - Sort by Payment Method
  - Direction indicators work

- [ ] **Search/Filter**
  - Search by donor name → Filters correctly
  - Search by amount → Filters correctly
  - Filter by category → Works
  - Filter by payment method → Works
  - Clear all filters button works

- [ ] **Donation Rows**
  - Donor name is visible
  - Amount is formatted with currency
  - Date is formatted readably
  - Category badge is colored
  - Payment method icon/text is shown
  - Receipt status is indicated

- [ ] **Actions**
  - Edit donation → Opens edit form
  - Send receipt → Triggers email (check inbox)
  - Delete donation → Shows confirmation
  - View details → Opens detail modal/page

- [ ] **Empty State**
  - Filter to show no donations → Empty state appears
  - Illustration is donation-themed
  - "Add Donation" CTA is prominent
  - Message encourages adding first donation

- [ ] **Pagination**
  - Works correctly with large datasets
  - Page size selector works (10, 25, 50, 100)
  - Entry count is accurate

---

## ➕ Donation Creation Form (`/donations/create`)

- [ ] **Category Selection**
  - Dropdown populates with categories
  - Select category → Value is saved
  - Categories include: Tithe, Offering, Building Fund, Mission, Other
  - Selection is visually clear

- [ ] **Payment Method**
  - Options: Cash, Check, Credit Card, Bank Transfer, Online
  - Click option → Highlights as selected
  - Selection is saved with form
  - Icons accompany each method (if implemented)

- [ ] **Amount Presets**
  - Preset buttons visible ($25, $50, $100, $250)
  - Click preset → Amount field fills
  - Can override preset with custom amount
  - Presets highlight when selected

- [ ] **Amount Input**
  - Accepts decimal values
  - Formats with currency symbol
  - Validates positive numbers only
  - Shows error for invalid input

- [ ] **Date Picker**
  - Opens calendar on click
  - Can select past dates
  - Cannot select future dates (if business rule)
  - Default is today's date
  - Selected date displays clearly

- [ ] **Member Search**
  - Type to search members
  - Autocomplete suggestions appear
  - Select member → Member is associated
  - Can clear selection
  - Shows "No members found" if none match

- [ ] **Notes Field**
  - Multi-line textarea
  - Accepts long text
  - Character count (if implemented)
  - Preserves line breaks

- [ ] **Form Validation**
  - Required fields marked with asterisk
  - Submit without required fields → Errors shown
  - Amount must be > 0
  - Date must be valid
  - Error messages are clear and helpful

- [ ] **Form Submission**
  - Valid form → Success message appears
  - Redirects to donations list
  - New donation appears in list
  - Receipt sent if option selected

- [ ] **Cancel Button**
  - Returns to donations list
  - Unsaved changes warning (if changes made)

---

## 👤 Admin Users Management (`/admin/users`)

- [ ] **Search Functionality**
  - Search by name → Filters correctly
  - Search by email → Filters correctly
  - Real-time filtering with debounce
  - Clear search works

- [ ] **Role Filter**
  - Filter by Admin → Shows only admins
  - Filter by Staff → Shows only staff
  - Filter by Volunteer → Shows only volunteers
  - Multiple role selection (if implemented)
  - Active filter is highlighted

- [ ] **Status Filter**
  - Active → Shows active users
  - Inactive → Shows inactive users
  - Filter works correctly

- [ ] **User Avatars**
  - Circular avatars with initials
  - Consistent sizing
  - Varied background colors
  - Load before other content

- [ ] **Last Login Info**
  - Shows relative time ("2 hours ago")
  - Shows "Never" if never logged in
  - Tooltip with exact timestamp (if implemented)
  - Updates on page refresh

- [ ] **Status Badges**
  - Active → Green badge
  - Inactive → Gray/Red badge
  - Clear text labeling

- [ ] **Status Toggle**
  - Click toggle → Status changes immediately
  - Visual feedback on toggle
  - Confirmation if deactivating admin
  - Persists after refresh

- [ ] **Bulk Actions**
  - Select multiple users with checkboxes
  - "Select All" checkbox works
  - Bulk delete button appears when selected
  - Bulk role change dropdown (if implemented)
  - Confirmation dialog for bulk actions
  - Actions apply to all selected users

- [ ] **Create User Form**
  - Name field validates
  - Email field validates (unique check)
  - Password field with visibility toggle
  - Password strength indicator
  - Role dropdown with all roles
  - Status toggle default to Active
  - Form validation prevents invalid submission
  - Success message on creation

- [ ] **Edit User**
  - Pre-fills existing user data
  - Can update all fields
  - Password field optional (leave blank to keep)
  - Changes save correctly
  - Cancel discards changes

- [ ] **Delete User**
  - Confirmation dialog appears
  - Warning about data implications
  - Cannot delete self (current user)
  - Deletes successfully on confirm

---

## 📄 Reports Management (`/reports`)

- [ ] **Report Type Filter**
  - Dropdown with report types
  - Financial, Membership, Attendance, Giving, etc.
  - Filtering works correctly
  - Type badges show on reports

- [ ] **Date Range Presets**
  - Same presets as other pages
  - Filters reports by date range
  - Works correctly

- [ ] **Search**
  - Search by report name
  - Search by description
  - Real-time filtering

- [ ] **Report Icons**
  - Each report type has unique icon
  - Icons are visually distinct
  - Icons match report category

- [ ] **Description Preview**
  - Long descriptions truncate with ellipsis
  - Tooltip shows full description on hover
  - Readable font size

- [ ] **Type Badges**
  - Colored by report type
  - Clear text labels
  - Consistent styling

- [ ] **Timestamp Display**
  - Shows "Created X ago" format
  - Shows last modified date
  - Accurate timestamps

- [ ] **Export Buttons**
  - PDF export button → Downloads PDF
  - Excel export button → Downloads .xlsx
  - CSV export button → Downloads .csv
  - Exports contain correct data
  - Export respects current filters

- [ ] **Create Report Form**
  - Name field required
  - Type dropdown required
  - Description field (optional)
  - Date range selection
  - Form validates correctly
  - Success on creation

- [ ] **Clear Filters**
  - Resets all filters to default
  - Shows all reports
  - Button is accessible

---

## ♿ Accessibility (All Pages)

- [ ] **Keyboard Navigation**
  - Tab through all interactive elements
  - Focus order is logical
  - No keyboard traps
  - Can submit forms with Enter key
  - Can close modals with Escape key

- [ ] **Focus Indicators**
  - All focused elements have visible outline
  - Outline is high contrast
  - Focus moves smoothly between elements

- [ ] **Skip to Content**
  - Skip link appears on tab
  - Activating skips to main content
  - Works on all pages

- [ ] **ARIA Labels**
  - Icon buttons have aria-label
  - Form fields have associated labels
  - Regions have landmark roles
  - Dynamic content announces changes

- [ ] **Screen Reader Testing**
  - NVDA/JAWS reads page correctly
  - Form errors are announced
  - Success messages are announced
  - Table headers are read for cells
  - Modal opening/closing is announced

- [ ] **Color Contrast**
  - Text meets 4.5:1 contrast ratio
  - UI components meet 3:1 ratio
  - Not relying on color alone for information
  - Test with grayscale filter

- [ ] **Text Scaling**
  - Zoom to 200% → Content remains usable
  - No text cutoff or overlap
  - Layout adapts appropriately

- [ ] **Motion Sensitivity**
  - Animations respect prefers-reduced-motion
  - No auto-playing animations
  - Transitions can be disabled

---

## 📱 Responsive Design (All Pages)

- [ ] **Mobile (320px - 480px)**
  - Navigation collapses to hamburger menu
  - Tables scroll horizontally or stack
  - Forms stack vertically
  - Touch targets are 44px minimum
  - No horizontal scrolling
  - Text is readable without zooming

- [ ] **Tablet (768px - 1024px)**
  - Layout adapts to medium screens
  - Grid columns reduce appropriately
  - Sidebars may collapse
  - Tables remain usable

- [ ] **Desktop (1280px+)**
  - Full layout displays
  - No wasted space
  - Optimal line lengths for reading

- [ ] **Touch Interactions**
  - Swipe gestures work (if implemented)
  - Pull to refresh (mobile)
  - Tap targets are large enough
  - No hover-dependent functionality

---

## ⚡ Performance (All Pages)

- [ ] **Load Times**
  - Initial page load < 2 seconds
  - Subsequent navigation < 1 second
  - Search results appear within 300ms after typing stops

- [ ] **Smooth Interactions**
  - No jank during scrolling
  - Animations run at 60fps
  - No layout shift during loading

- [ ] **Resource Usage**
  - Images are optimized
  - Lazy loading for off-screen images
  - No memory leaks during extended use

- [ ] **Network Efficiency**
  - Debounced search reduces API calls
  - Pagination limits data transfer
  - Assets are cached appropriately

---

## 🌐 Cross-Browser Testing

### Chrome (Latest)
- [ ] All features work correctly
- [ ] No console errors
- [ ] Performance is acceptable

### Firefox (Latest)
- [ ] All features work correctly
- [ ] No console errors
- [ ] Performance is acceptable

### Safari (Latest)
- [ ] All features work correctly
- [ ] No console errors
- [ ] Performance is acceptable

### Edge (Latest)
- [ ] All features work correctly
- [ ] No console errors
- [ ] Performance is acceptable

### Mobile Safari (iOS)
- [ ] Responsive layout works
- [ ] Touch interactions work
- [ ] No crashes or freezes

### Chrome Mobile (Android)
- [ ] Responsive layout works
- [ ] Touch interactions work
- [ ] No crashes or freezes

---

## ✅ Sign-off Criteria

**Before marking testing complete:**
- [ ] All critical bugs fixed
- [ ] All high-priority bugs documented
- [ ] Accessibility violations addressed
- [ ] Performance benchmarks met
- [ ] Cross-browser compatibility verified
- [ ] Mobile responsiveness confirmed
- [ ] Documentation updated

**Total Test Cases:** 200+  
**Estimated Testing Time:** 4-6 hours  
**Recommended Testers:** 2-3 people  

---

## Bug Reporting Template

```markdown
### Bug Title
[Concise description]

### Severity
Critical / High / Medium / Low

### Page/Feature
[Where does it occur?]

### Steps to Reproduce
1. 
2. 
3. 

### Expected Behavior
[What should happen?]

### Actual Behavior
[What actually happens?]

### Environment
- Browser: 
- OS: 
- Device: 
- Screen Size: 

### Screenshots/Video
[Attach if applicable]

### Additional Notes
[Any other relevant information]
```
