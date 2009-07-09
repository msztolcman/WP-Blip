=== Plugin Name ===
Contributors: mysz
Tags: blip, microblogging, widget
Requires at least: 2.7
Tested up to: 2.8
Stable tag: 0.5.1

WP-Blip! służy do wyświetlania ostatnich wpisów z Blip!a w Wordpressie.

== Description ==

WP-Blip! pozwala na wyświetlenie ostatnich wpisów z [BlipLoga](http://blip.pl).
Można wyświetlać wszystkie wpisy, lub też ograniczone do dowolnego tagu. Udostępniony jest w części administracyjnej panel pozwalający na dowolną konfigurację sposoby wyświetlanych danyc, ich ilości czy innych limitów.

Proszę o zgłaszanie wszelkich uwag i problemów na tagu [#wpblip](http://blip.pl/tags/wpblip) lub do użytkownika [^mysz](http://blip.pl/users/mysz/dashboard), ewentualnie można zgłosić ticketa w [Google Code](http://code.google.com/p/wp-blip/issues/list).

== Installation ==

1. Wejdź do panelu administracyjnego, przejdź do **Wtyczki** -> **Dodaj nową**.
2. W okno wyszukiwarki wpisz **WP-Blip!** i wciśnij *Enter*.
3. Na liście wtyczek znajdź pozycję **WP-Blip!** i znajdź po prawej stronie link **Zainstaluj**. Kliknij go.
4. Otworzy się okienko z opisem wtyczki - kliknij znajdujący się po prawej stronie przycisk **Zainstaluj**.
5. W tej chwili wtyczka jest już zainstalowana - na dole znajdziesz link **Włącz wtyczkę**. Kliknij na nim.
6. Voila! Wtyczka jest zainstalowana :)

7. Teraz masz dwie opcje do wyboru:
 1. Jeśli używasz skórki obsługującej widgety: Przejdź do **Wygląd** -> **Widgety**, przeciągnij **WP-Blip!** do miejsca w którym chcesz wyświetlać statusy, uzupełnij tytuł, i zapisz zmiany.
 2. W przeciwnym wypadku musisz ręcznie dodać odpowiedni wpis do skórki: W szablonie, w miejscu w którym ma się wyświetlić lista wpisów, dodaj kod:
    &lt;?php
    if (function_exists ('wp_blip')) { wp_blip("\n", 1); }
    ?&gt;

8. W **Ustawienia** -> **WP-Blip!** możesz skonfigurować sposób działania wtyczki.

== Frequently Asked Questions ==

= Gdzie mogę zgłosić błąd lub poprosić o jakąś nową funkcjonalność? =

Proszę o zgłaszanie wszelkich uwag i problemów na tagu [#wpblip](http://blip.pl/tags/wpblip) lub do użytkownika [^mysz](http://blip.pl/users/mysz/dashboard), ewentualnie można zgłosić ticketa w [Google Code](http://code.google.com/p/wp-blip/issues/list).

= Czemu włączenie rozwijania linków statusów lub linków z serwisu rdir.pl spowalnia ładowanie? =

Ponieważ dla każdego linku z osobna trzeba pobrać dodatkowe dane z serwisu Blip!

== Screenshots ==
1. Rozwinięty widget WP-Blip!
2. Panel administracyjny

== Changelog ==
= 0.5.1 =
* wysłany nagłówek Cache-Control z wartością no-cache powoduje ponowne wczytanie statusów
* zabezpieczenie przed parsowaniem pliku z opcjami poprzez bezpośrednie odwołanie do wp-blip-options.php

= 0.5.0 =
* możliwość użycia WP-Blip! jako widgetu (ciągle można używać w wersji bez-widgetowej!) (issue #1)
* możliwość zmiany sposobu wyświetlania daty. Teraz data może być absolutna, relatywna i relatywna uproszczona (issue #2)
* poprawki błędów:
 - pl-znaczki nie były uwzględniane w nazwach tagów
 - WP-Blip! gryzł się nieco z WP-BlipBot-em (i/lub innymi wtyczkami korzystającymi z modularnego BlipApi.php) - nie umiały nawzajem korzystać z pluginów blipapi które nie są w katalogu pierwszej wczytanej wersji BlipApi.php
* porządki w kodzie

= 0.4.8 =
* w niektórych sytuacjach sypało się rozwijanie linków i nazw userów
= 0.4.7 =
* możliwość włączenia rozwijania linków z rdir.pl
* możliwość włączenia pobierania podlinkowanych statusów i wyświetlanie ich w title linku (po przytrzymaniu kursora nad odnośnikiem)
* poprawki błędów:
 - błędy przy pobieraniu statusów przy ograniczeniu dla konkretnych tagów
 - błędnie linkował adresy inne niż blip.pl - zła kolejność podmieniania adresów
 - w treści wpisów nie były enkodowane znaki wrażliwe dla HTML
= 0.4.6 =
* nie jest już potrzebne podawanie hasła do Blip!a
* można wyświetlać wpisy dowolnego usera
= 0.4.5 = 
* wykrywanie i linkowanie urli do innych wiadomości na blipie - nie są one puszczane przez rdir.pl
* rozpoznawanie linkow https
= 0.4.4 =
* możliwość wyczyszczenia cache z poziomu panelu
* możliwość zdefiniowania treści przed i za listą statusów
* porządki w kodzie i w układzie plików

== Wymagania ==
* PHP w wersji 5.2 lub wyżej
* działające rozszerzenie PHP curl

