For Tip Jar WP, here's all of the tables and what they do.

The Transactions Table
When the customer clicks the "pay" button, a transaction is created. An arrangement is also created, containing information about the tip (if it recurs, when, etc).

The Arrangements table
An arrangement is a list of info about the tip, and any future tips that might automatically recurr. Recurring tips will be cancelled in the arrangement.

New Transactions will create Arrangements.
Old Arrangements will be used to create new Transactions in the case of recurring-enabled arrangements.
Refunds will create new Transactions and update the Old/Related Arrangement.

The Forms Table.
The forms table contains all of the tipping forms. These are essentially "products", with specific limitations and requirements on tipping, like who the tip is for, the minimum tip amount, etc.
