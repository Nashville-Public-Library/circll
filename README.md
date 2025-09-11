# circll
Circulation client for Nashville Public Library / Limitless Libraries circulation staff using SIP2 and CarlXAPIs

## About
Circulation client for the Nashville Public Library's Limitless Libraries program, which delivers public library books to Metro Nashville Public Schools.

Unlike most public library circulation clients, this interface is designed to perform checkout in a centralized location, not in the presence of the patron. 

The flow is: 
* A patron places a hold set to be picked up at their school.
* The book is picked from the shelf and routed to the Limitless Libraries circulation team.
* The circulation team scans the book's barcode into this client.
* The client gathers information from the ILS and checks out the book to the patron.
* The client sets a due date for the book based on the school's closed days, the patron type, and the media type.
  * During configuration, the circll administrator supplies a set of dates that the schools are closed and a final due date for the school year.
  * The client first evaluates the date the book should arrive at the pickup location, assumed to be 3 business days after checkout... or the next day the schools are open following that date.
  * The client then evaluates the due date, using the arrives-by-date and adding the appropriate number of days based on the patron type and media type.
* The client prints a receipt with the due date and information that will get it where it needs to go (the pickup school's name, patron's homeroom, patron's name). (Nashville uses sticky thermal receipt paper and affixes these receipts to the back of the book.)

Currently, this script is customized for Nashville's needs, and will only work with the CARL.X integrated library system. If you are interested in using something similar in your library, please reach out to us!

Learn more about the Limitless Libraries program at [Limitless Libraries](https://www.limitlesslibraries.org/about)

## Receipt Printer Setup
Configuring Print Settings for Firefox Browser with CIRCLL Web Client

**1. To Enable Silent Printing (Automatic Printing without Dialog):**
  * Type `about:config` into the address bar and press Enter.
  * You may see a "Proceed with Caution" warning. Click "Accept the Risk and Continue" to proceed.
  * In the search bar that appears, type `print.always_print_silent`.
  * If this preference does not exist, you will see a button to "Add" or "Create" a new Boolean preference. Click this button.
  * Ensure the value for `print.always_print_silent` is set to `false` for now. (You'll set it to `true` later).

**2. Set Default Printer to the Re-Stick Printer:**
  * Go to `about:config` (if you're not already there).
  * In the search bar, type `print_printer`.
  * Locate the preference named `print.printer_name`.
  * Right-click on the highlighted `print.printer_name` preference and select "Modify" (or double-click it).
  * In the dialog box that appears, enter `EPSON TM-T88IV ReStick` as the value.
  * Click "OK" or press Enter.

**3. Finish Settings (Enable Automatic Silent Printing):**
  * In the about:config search bar, type `print.always_print_silent`.
  * Double-click the highlighted `print.always_print_silent` preference to toggle its value to `true`.
  * Restart Firefox for the changes to take effect.

**4. In Firefox, go to: https://circll.library.nashville.org/circll.php**

**6. Scan an item**

