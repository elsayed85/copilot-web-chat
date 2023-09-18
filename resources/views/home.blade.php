<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0"/>
    <meta name="description" content="A conversational AI system that listens, learns, and challenges"/>
    <meta property="og:title" content="ChatGPT"/>
    <meta property="og:image" content="https://openai.com/content/images/2022/11/ChatGPT.jpg"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        property="og:description"
        content="A conversational AI system that listens, learns, and challenges"/>
    <meta property="og:url" content="https://chat.acy.dev"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="{{ asset('img/apple-touch-icon.png') }}"/>
    <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="{{ asset('img/favicon-32x32.png') }}"/>
    <link
        rel="icon"
        type="image/png"
        sizes="16x16"
        href="{{ asset('img/favicon-16x16.png') }}"/>
    <link rel="manifest" href="{{ asset('img/site.webmanifest') }}"/>
    <link
        rel="stylesheet"
        href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@latest/build/styles/base16/dracula.min.css"/>
    <title>FreeGPT</title>
</head>

<body data-urlprefix="{{ url('/')  }}" data-authorized="{{ $hasGithubToken ? "true" : "false" }}">
<div class="main-container">
    <div class="box sidebar">
        <div class="top">
            <button class="button" onclick="new_conversation()">
                <i class="fa-regular fa-plus"></i>
                <span>{{_('New Conversation')}}</span>
            </button>
            <div class="spinner"></div>
        </div>
        <div class="sidebar-footer">

            @if($hasGithubToken)
                <form action="{{ route('logout') }}" method="post" class="form">
                    @csrf
                    <button class="button" type="submit">
                        <i class="fa-regular fa-trash"></i>
                        Logout
                    </button>
                </form>
            @else
                <button class="button" onclick="login_with_github()" id="github_login_button">
                    <i class="fa-regular fa-trash"></i>
                    <span>
                        Get Github Code
                    </span>
                </button>

                <input type="text" id="github_user_code"
                       style="display: none; border: none; background: none; color: white; width: 100%; text-align: center; font-size: 20px; font-weight: bold; margin-top: 10px; margin-bottom: 10px;"/>

                <a class="button" href="https://github.com/login/device" target="_blank">
                    <i class="fa-regular fa-trash"></i>
                    <span>
                        Go to Github Auth Page
                    </span>
                </a>
            @endif


            <button class="button" onclick="delete_conversations()">
                <i class="fa-regular fa-trash"></i>
                <span>{{_('Clear Conversations')}}</span>
            </button>

            <div class="settings-container">
                <div class="checkbox field">
                    <span>{{_('Dark Mode')}}</span>
                    <input type="checkbox" id="theme-toggler"/>
                    <label for="theme-toggler"></label>
                </div>
                <div class="field">
                    <span>{{_('Language')}}</span>
                    <select
                        class="dropdown"
                        id="language"
                        onchange="changeLanguage(this.value)"></select>
                </div>
            </div>
            <a class="info" href="#" target="_blank">
                <i class="fa-brands fa-github"></i>
                <span class="conversation-title"> {{_('Version')}}: 1.0.0 </span>
            </a>
        </div>
    </div>
    <div class="conversation">
        <div class="stop-generating stop-generating-hidden">
            <button class="button" id="cancelButton">
                <span>{{_('Stop Generating')}}</span>
            </button>
        </div>
        <div class="box" id="messages"></div>
        <div class="user-input">
            <div class="box input-box">
						<textarea
                            id="message-input"
                            placeholder="{{_('Ask a question')}}"
                            cols="30"
                            rows="10"
                            style="white-space: pre-wrap"></textarea>
                <div id="send-button">
                    <i class="fa-regular fa-paper-plane-top"></i>
                </div>
            </div>
        </div>
        <div>
            <div class="options-container">
                <div class="buttons">
                    <div class="field">
                        <select class="dropdown" name="model" id="model">
                            <option value="gpt-3.5-turbo" selected>GPT-3.5</option>
                            {{--                            <option value="gpt-3.5-turbo-16k">GPT-3.5-turbo-16k</option>--}}
                            {{--                            <option value="gpt-4" selected>GPT-4</option>--}}
                        </select>
                    </div>
                    <div class="field">
                        <select class="dropdown" name="jailbreak" id="jailbreak">
                            <option value="default" selected>{{_('Default')}}</option>
                            <option value="gpt-dan-11.0">{{_('DAN')}}</option>
                            <option value="gpt-evil">{{_('Evil')}}</option>
                        </select>
                    </div>
                </div>
                <div class="field checkbox">
                    <input type="checkbox" id="switch"/>
                    <label for="switch"></label>
                    <span>{{_('Web Access')}}</span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="menu-button">
    <i class="fa-solid fa-bars"></i>
</div>

<!-- scripts -->
<script>
    window.conversation_id = "";
</script>
<script src="{{ asset('js/icons.js') }}"></script>
<script src="{{ asset('js/chat.js') }}" defer></script>
<script src="https://cdn.jsdelivr.net/npm/markdown-it@latest/dist/markdown-it.min.js"></script>
<script src="{{ asset('js/highlight.min.js') }}"></script>
<script src="{{ asset('js/highlightjs-copy.min.js') }}"></script>
<script src="{{ asset('js/theme-toggler.js') }}"></script>
<script src="{{ asset('js/sidebar-toggler.js') }}"></script>
<script src="{{ asset('js/change-language.js') }}"></script>
<script src="{{ asset('js/main.js') }}" defer></script>
</body>
</html>
