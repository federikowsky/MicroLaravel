<?php

namespace App\Core;

class Flash
{
    const FLASH = 'FLASH_MESSAGES';

    const FLASH_ERROR = 'error';
    const FLASH_WARNING = 'warning';
    const FLASH_INFO = 'info';
    const FLASH_SUCCESS = 'success';

    public function __construct()
    {
        if (!session()->has(self::FLASH)) {
            session()->set(self::FLASH, []);
        }
    }

    /**
    * Create a flash message
    *
    * @param string $name
    * @param string $message
    * @param string $type
    * @return void
    */
    private function create(string $name, string $message, string $type): void
    {
        // remove existing message with the name
        if (session()->has(self::FLASH . $name)) {
            session()->remove(self::FLASH . $name);
        }
        // add the message to the session
        session()->set(self::FLASH, [
            $name => ['message' => $message, 'type' => $type]
        ]);
    }

    /**
    * Format a flash message
    *
    * @param array $flash_message
    * @return string
    */
    private function format(array $flash_message): string
    {
        return sprintf('<div class="alert alert-%s">%s</div>',
            $flash_message['type'],
            $flash_message['message']
        );
    }

    /**
    * Display a flash message
    *
    * @param string $name
    * @return void
    */
    private function display(string $name): void
    {
        if (!session()->has(self::FLASH . $name)) {
            return;
        }

        // get message from the session
        $flash_message = session()->get(self::FLASH, $name);

        // delete the flash message
        session()->remove(self::FLASH . $name);

        // display the flash message
        echo self::format($flash_message);
    }

    /**
    * Display all flash messages
    *
    * @return void
    */
    private function displayAll(): void
    {
        if (!session()->has(self::FLASH)) {
            return;
        }

        // get flash messages
        $flash_messages = session()->get(self::FLASH);

        // remove all the flash messages
        session()->remove(self::FLASH);

        // show all flash messages
        foreach ($flash_messages as $flash_message) {
            echo self::format($flash_message);
        }
    }

    /**
    * Flash a message
    *
    * @param string $name
    * @param string $message
    * @param string $type (error, warning, info, success)
    * @return void
    */
    public function flash($name, $message, string $type): void
    {
        if ($name !== '' && $message !== '' && $type !== '') {
            // create a flash message
            self::create($name, $message, $type);
        } elseif ($name !== '' && $message === '' && $type === '') {
            // display a flash message
            self::display($name);
        } elseif ($name === '' && $message === '' && $type === '') {
            // display all flash messages
            self::displayAll();
        }
    }
}