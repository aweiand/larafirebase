### Introduction

**Larafirebase** is a package thats offers you to send push notifications or custom messages via Firebase in Laravel.

Firebase Cloud Messaging (FCM) is a cross-platform messaging solution that lets you reliably deliver messages at no cost.

For use cases such as instant messaging, a message can transfer a payload of up to 4KB to a client app.

### Installation

Follow the steps below to install the package.


**Composer**

```
composer require aweiand/larafirebase
```

**Copy Config**

Run `php artisan vendor:publish --provider="Aweiand\Larafirebase\Providers\LarafirebaseServiceProvider"` to publish the `larafirebase.php` config file.

**Get Athentication Key**

Get Authentication Key from https://console.firebase.google.com/

**Configure larafirebase.php as needed**

```
'firebase_credentials_file' => '{PATH_TO_THE_CREDENTIALS_FILE}',
'firebase_project_id' =>       '{FIREBASE_PROJECT_ID}'
```

### Usage

Follow the steps below to find how to use the package.

Example usage in **Controller/Service** or any class:

```php
use Aweiand\Larafirebase\Facades\Larafirebase;

class MyController
{
    private $deviceTokens ='{TOKEN_1}';

    public function sendNotification()
    {
        return Larafirebase::withTitle('Test Title')
            ->withBody('Test body')
            ->withImage('https://firebase.google.com/images/social.png')
            ->withIcon('https://seeklogo.com/images/F/firebase-logo-402F407EE0-seeklogo.com.png')
            ->withSound('default')
            ->withClickAction('https://www.google.com')
            ->withPriority('high')
            ->withAdditionalData([
                'color' => '#rrggbb',
                'badge' => 0,
            ])
            ->sendNotification($this->deviceTokens);
        
        // Or
        return Larafirebase::fromArray(['title' => 'Test Title', 'body' => 'Test body'])->sendNotification($this->deviceTokens);
    }

    public function sendMessage()
    {
        return Larafirebase::withTitle('Test Title')
            ->withBody('Test body')
            ->sendMessage($this->deviceTokens);
            
        // Or
        return Larafirebase::fromArray(['title' => 'Test Title', 'body' => 'Test body'])->sendMessage($this->deviceTokens);
    }
}
```

Example usage in **Notification** class:

```php
use Illuminate\Notifications\Notification;
use Aweiand\Larafirebase\Messages\FirebaseMessage;

class SendBirthdayReminder extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['firebase'];
    }

    /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        $deviceTokens = [
            '{TOKEN_1}',
            '{TOKEN_2}'
        ];
        
        return (new FirebaseMessage)
            ->withTitle('Hey, ', $notifiable->first_name)
            ->withBody('Happy Birthday!')
            ->asNotification($deviceTokens); // OR ->asMessage($deviceTokens);
    }
}
```


### Tips
- Check example how to receive messages or push notifications in a [JavaScript client](/javascript-client).
- You can use `larafirebase()` helper instead of Facade.


### Payload

Check how is formed payload to send to firebase:

Example 1:

```php
Larafirebase::withTitle('Test Title')->withBody('Test body')->sendNotification('token1');
```

```json
{
  "registration_ids": [
    "token1"
  ],
  "notification": {
    "title": "Test Title",
    "body": "Test body"
  },
  "priority": "normal"
}
```

Example 2:

```php
Larafirebase::withTitle('Test Title')->withBody('Test body')->sendMessage('token1');
```

```json
{
  "registration_ids": [
    "token1"
  ],
  "data": {
    "title": "Test Title",
    "body": "Test body"
  }
}
```

If you want to create payload from scratch you can use method `fromRaw`, for example:

```php
return Larafirebase::fromRaw([
    'registration_ids' => ['token1', 'token2'],
    'data' => [
        'key_1' => 'Value 1',
        'key_2' => 'Value 2'
    ],
    'android' => [
        'ttl' => '1000s',
        'priority' => 'normal',
        'notification' => [
            'key_1' => 'Value 1',
            'key_2' => 'Value 2'
        ],
    ],
])->send();
```

---

<sup>Made with ♥ by Augusto Weiand ([@aweiand](https://github.com/aweiand)).</sup>
