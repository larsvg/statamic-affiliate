<?php

// config for Larsvg/StatamicAffiliate
return [
    'mail_feed_item_updates_to' => env('MAIL_FEED_ITEM_UPDATES_TO', ''),
    'mail_on_new_items'         => env('MAIL_ON_NEW_ITEMS', true),
    'mail_on_updated_item'      => env('MAIL_ON_UPDATED_ITEMS', false),
    'mail_on_deleted_item'      => env('MAIL_ON_DELETED_ITEMS', false),

    'enhance_with_ai'                 => env('ENHANCE_WITH_AI', false),
    'max_ai_enhanced_items_per_batch' => env('MAX_AI_ENHANCED_ITEMS_PER_BATCH', 10),
    'ai_prompt'                       => env('AI_PROMPT','Write a good product description for the website of an affiliate webshop based on the following text. Input: Title: {productName}, Description: {productDescription}'),
    'ai_prompt_with_categories'       => env('AI_PROMPT_CATEGORIES','Write a good product description for the website of an affiliate webshop based on the following text. Input: Title: {productName}, Description: {productDescription}, Categories: {categories}'),
];
