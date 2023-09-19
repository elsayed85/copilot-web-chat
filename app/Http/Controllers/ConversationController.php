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
        $internet_access = request()->input('meta.content.internet_access');
        $conversation[] = $message;

        $service = new \App\Services\CopilotApi();

        if ($internet_access) {
            $results = $service->fetch_search_results($message['content']);

            if (count($results)) {
                $conversation[] = $results[0];
            }
        }

        $reply = $service->init()->setMessages($conversation)->send();

        return response()->stream(function () use ($reply) {
            foreach ($reply as $response) {
                usleep(10000);
                $text = $response->choices[0]->content;
                if (connection_aborted()) {
                    break;
                }
                echo $text;
                ob_flush();
                flush();
            }

            echo "\n\n";
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
