# WordPress-Plugin
 Custom Book Plugin
Overview
This WordPress plugin creates a custom post type "Book" with additional functionality for managing books, authors, genres, ratings, and dynamic frontend interactions.

The plugin includes:

Custom post type Book

Custom taxonomies (Genre, Author)

Custom fields (Publication Year, Rating)

Shortcodes for displaying book info

AJAX functionality for filtering and rating

Automatic email notifications when a new book is published

Features

Custom Post Type

Registers a new post type: Book.

Custom Taxonomies

Genre (taxonomy for categorizing books).

Author (taxonomy for book authors).

Custom Fields

Publication Year field (stored as post meta).

Rating field with AJAX-powered updates.

Shortcode [book_info]

Displays book information on the frontend (Title, Author, Genre, Rating, Publication Year).

Accepts a parameter years to filter books published in the last X years.

Custom Styles & Scripts

Enqueues plugin-specific CSS & JS files only on pages where [book_info] is used.

Meta Box for Publication Year

Provides an editor screen meta box for entering the publication year.

Custom Action (Email Notification)

Sends an email notification to the author when a new book is published.

Meta Query for Filtering Recent Books

[book_info years="5"] shows only books published in the last 5 years (default).

AJAX-based Genre Filtering

Dynamically loads books by genre on the frontend without refreshing the page.

AJAX-based Book Rating

Allows users to rate books on the frontend.

Saves ratings in the database and updates instantly.

Frontend Filtering by Rating

Users can filter books based on ratings.
