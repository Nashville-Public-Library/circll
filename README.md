# circll
Circulation client for Nashville Public Library / Limitless Libraries circulation staff using SIP2 and CarlXAPIs
**The following documention outlines print configuration settings for the Firefox browser while using the CIRCLL Web Client**

**1. To set automatic printing:**
  * Type **about:config** into the address bar.
  * Right-click anywhere in the resulting field.
  * select New > Boolean
  * Enter preference name: print.always_print_silent
  * For now, set value to **false**.

**2. Set default printer to the Re-Stick printer:**
  * Go back to about:config
  * search "print_printer"
  * Right-click on the highlighted line
  * select "modify"
  * Enter "EPSON TM-T88IV ReStick"
  * Select "OK"

**3. Finish settings**
  * Search "print.always_print_silent" 
  * Double-click the highlighted line to set value to "true". 
  * Restart Firefox. 

4. Navigate the Firefox browser to: https://galacto.library.nashville.org/circll.php
5. Scanning the item barcode should create an automatic reciept from the re-stick printer. 

if this is not the case:

To Be Continued
