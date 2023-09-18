<?php

namespace App\Http\Controllers;

class ConversationController extends Controller
{
    /**
     * @throws \Exception
     */
    public function handel()
    {
        $message = request()->input('meta.content.parts')[0];
        $conversation = request()->input('meta.content.conversation') ?? [];
        $conversation[] = $message;

        $service = new \App\Services\CopilotApi();
        $response = $service->init()->setMessages($conversation)->send();

        echo $response;
    }

    public function getLanguages()
    {
        return response()->json([
            'en',
            'ar',
        ]);
    }

    public function getLocale()
    {
        return response()->json(app()->getLocale());
    }

    public function changeLanguage()
    {
        $language = request()->get('language');

        app()->setLocale($language);

        return response()->json($language);
    }
}
