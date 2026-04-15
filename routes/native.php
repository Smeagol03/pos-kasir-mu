<?php

use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\Window;

// Main application window configuration
Window::open('main')
    ->title('POS Kasir MU')
    ->url('/dashboard')
    ->width(1400)
    ->height(900)
    ->minWidth(1024)
    ->minHeight(768)
    ->center()
    ->resizable()
    ->maximizable()
    ->minimizable()
    ->focusable()
    ->alwaysOnTop(false)
    ->showDevTools(false) // Set to true for development/debugging
    ->rememberState(); // Remember window position and size

// Application menu
Menu::create(
    Menu::app(),
    Menu::window()
);
