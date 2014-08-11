# News to Newsletter

You want to send a newsletter every single time a news will be published? Here you go.

## How-to


### Requirements

1. (notification_center)[https://github.com/terminal42/contao-notification_center] installed
2. News module enabled
3. Newsletter module enabled


### Configuration


#### Notification Center
[Manual of the Notification Center (German)][1]

##### Gateway

Setup "Standard email gateway"

##### Notifications

New notification
- Type: News newsletter - Default

New message
- Choose the gateway you added
- Check "Publish message"

Manage languages and add new language
- Select a language and activate "Fallback"
- Simple Tokens:
 - ##news_*##
 - ##news_archive_*##
 - ##news_headline##
 - ##news_teaser##
 - ##news_text## (content elements)
 - ##news_url##


#### Newsletter (Contao)

- Add new channel


#### News

- Add news archive and enable newsletter in the setting
- Choose the newsletter channel and the notification
- Write a new article and do not publish it by check "Publish item"
- Save and close
- In the news overview click on the envelope icon
- A message shows up saying "Newsletter sent successfully!"

[1]: https://isotopeecommerce.org/de/handbuch/v/2.1/r/benachrichtigszentrum.html