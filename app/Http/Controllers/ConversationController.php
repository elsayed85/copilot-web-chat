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

        $reply = $service->init()->setMessages($conversation)->send();

        return response()->stream(function () use ($reply) {
            foreach ($reply as $response) {
                $text = $response->choices[0]->text;
                if (connection_aborted()) {
                    break;
                }
                echo $text;
                ob_flush();
                flush();
            }

            ob_flush();
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
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
