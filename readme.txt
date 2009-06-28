=== Plugin Name ===
Contributors: mysz
Tags: blip, microblogging
Requires at least: 2.7
Tested up to: 2.8
Stable tag: 0.4.6

WP-Blip! służy do wyświetlania ostatnich wpisów z Blip!a w Wordpressie.

== Description ==

WP-Blip! pozwala na wyświetlenie ostatnich wpisów z BlipLoga.
Można wyświetlać wszystkie wpisy, lub też ograniczone do dowolnego tagu. Udostępniony jest w części administracyjnej panel pozwalający na dowolną konfigurację sposoby wyświetlanych danyc, ich ilości czy innych limitów.

Proszę o zgłaszanie wszelkich uwag i problemów na tagu [#wpblip](http://blip.pl/tags/wpblip) lub do użytkownika [^mysz](http://blip.pl/users/mysz/dashboard), ewentualnie można zgłosić ticketa w [Google Code](http://code.google.com/p/wp-blip/issues/list).

== Installation ==

1. Rozpakuj wp-blip.X.X.X.zip do dowolnego folderu w WORDPRESS_ROOT/wp-content/plugins (zostanie utworzony katalog wp-blip).
2. Przejdź do panelu administracyjnego, i aktywuj wtyczkę.
3. W Ustawieniach -> WP-Blip! możesz skonfigurować sposób działania wtyczki.
4. W szablonie, w miejscu w którym ma się wyświetlić lista wpisów, dodaj kod:
<?php
if (function_exists ('wp_blip')) { wp_blip("\n", 1); }
?>

== Frequently Asked Questions ==

= Gdzie mogę zgłosić błąd lub poprosić o jakąś nową funkcjonalność? =

Proszę o zgłaszanie wszelkich uwag i problemów na tagu [#wpblip](http://blip.pl/tags/wpblip) lub do użytkownika [^mysz](http://blip.pl/users/mysz/dashboard), ewentualnie można zgłosić ticketa w [Google Code](http://code.google.com/p/wp-blip/issues/list).

== Screenshots ==
1. Panel administracyjny

== Changelog ==
= 0.4.6 =
* nie jest już potrzebne podawanie hasła do Blip!a
* można wyświetlać wpisy dowolnego usera
= 0.4.5 = 
* wykrywanie i linkowanie urli do innych wiadomosci na blipie - nie sa one puszczane przez rdir.pl
* rozpoznawanie linkow https
= 0.4.4 =
* możliwość wyczyszczenia cache z poziomu panelu
* możliwość zdefiniowania treści przed i za listą statusów
* porządki w kodzie i w układzie plików

== Requirements ==
* PHP w wersji 5.2 lub wyżej
* działające rozszerzenie PHP curl

