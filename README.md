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
**The following documentation outlines print configuration settings for the Firefox browser while using CIRCLL Web Client**

**1. To set automatic printing:**
  * Type `about:config` into the address bar.
  * Right-click anywhere in the page.
  * select **New** > **Boolean**
  * Enter preference name: `print.always_print_silent`
  * For now, set value to **false**.

**2. Set default printer to the Re-Stick printer:**
  * Go to `about:config`
  * search for `print_printer`
  * Right-click on the highlighted line
  * select **Modify**
  * Enter `EPSON TM-T88IV ReStick`
  * Select **OK**

**3. Finish settings**
  * Search `print.always_print_silent`
  * Double-click the highlighted line to set value to **true**
  * Restart Firefox.

**4. In Firefox, go to the circll page, e.g., ** https://circll.library.nashville.org/circll.php

**5. Scan an item**
