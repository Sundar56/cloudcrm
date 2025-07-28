<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateGeneralSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-general-settings {companydbname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $company_db = $this->argument('companydbname');
        error_log("Param 1: $company_db");
        $dbHost = env("DB_ROOT_HOST");
        if ($dbHost == 'localhost') {
            $dbUsername = 'root';
            $dbPassword = null;
        } else {
            $dbUsername =  env("DB_ROOT_USERNAME");
            $dbPassword =  env("DB_ROOT_PASSWORD");
        }
        config([
            "database.connections.$company_db" => [
                'driver' => 'mysql',
                'host' => env($dbHost, '127.0.0.1'),
                'port' => env('DB_ROOT_PORT', '3306'),
                'database' => $company_db,
                'username' => $dbUsername,
                'password' => $dbPassword,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        ]);

        try {
            DB::connection($company_db)->getPdo();
            error_log('Successfully connected to the databas::' . $company_db);
        } catch (\Exception $e) {
            error_log('MySQL connection failed::' . $e->getMessage());
            return; // Stop execution
        }
        $general_settings = array(
            array("id" => "1", "variabel" => "SiteDomain", "vaerdi" => "example.org", "beskrivelse" => "Det aktive top-domain", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "2", "variabel" => "SuccessFullLogin", "vaerdi" => "https://sso.example.org/internStart", "beskrivelse" => "URL til redir ved successfull login", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "3", "variabel" => "SSOPath", "vaerdi" => "https://sso.example.org/", "beskrivelse" => "URL til SSO Login", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "4", "variabel" => "GemCookieDage", "vaerdi" => "30", "beskrivelse" => "Antal dage Cookies gemmes", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "5", "variabel" => "invalidPWCookieText", "vaerdi" => "Den gemte adgangskode blev ikke accepteret!", "beskrivelse" => "Tekst ved forkert adgangskode", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "6", "variabel" => "invalidUserCookieText", "vaerdi" => "Det gemte brugernavn blev ikke fundet!", "beskrivelse" => "Tekst ved forkert eller ukendt brugernavn", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "7", "variabel" => "invalidPWText", "vaerdi" => "Den angivne password blev ikke accepteret!", "beskrivelse" => "Tekst,ved forkert password", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "8", "variabel" => "invalidUserText", "vaerdi" => "Det angivne brugernavn blev ikke fundet!", "beskrivelse" => "Tekst,ved forkert eller ukendt brugernavn", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "9", "variabel" => "FejlText", "vaerdi" => "F&oslash;lgende fejl opstod: ", "beskrivelse" => "Generel tekst ved fejl. Vær opmærksom på trailing space.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "10", "variabel" => "DoneText", "vaerdi" => "Handlingen blev gennemf&oslash;rt!", "beskrivelse" => "Tekst,ved korrekt gennemført handling.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "11", "variabel" => "notsame", "vaerdi" => "Du skal indtaste det samme password i begge felter!", "beskrivelse" => "Tekst,hvis Adgangskode og gentag adgangskode ikke er ens.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "12", "variabel" => "empty", "vaerdi" => "Du skal indtaste et password,for at kunne skifte dit password!", "beskrivelse" => "Tekst,hvis der bliver førsøgt at rette en adgangskode,men feltet er tomt.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "13", "variabel" => "AccessDenied", "vaerdi" => "Du har fors&oslash;gt at tilg&aring; en side", "beskrivelse" => "hvortil dit login ikke giver adgang!", "sender_email" => "Tekst, hvis brugeren forsøger at tilg&aring; en side, dennes login ikke er godkendt til.",  "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "14", "variabel" => "fanefarve", "vaerdi" => "d9F", "beskrivelse" => "Farven på fanerne under firma-oplysninger", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "15", "variabel" => "menuWidth", "vaerdi" => "250", "beskrivelse" => "Bredden på venstre-menu", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "16", "variabel" => "menuColor", "vaerdi" => "930312", "beskrivelse" => "Farve på venstre-menu", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "17", "variabel" => "menuAlwaysShow", "vaerdi" => "1", "beskrivelse" => "Skal venstremenuen altid vises? 1 hvis ja, 0 hvis nej.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "19", "variabel" => "VelkomstMail", "vaerdi" => "Kære XXnavnXX Velkommen hos SeeSafe IT. I forbindelse med oprettelsen, har vi tilmeldt dig vores nyhedsbrev. Vi sender spændende nyheder en til to gange om måneden. Du kan altid framelde dig nederst i nyhedsbrevet. Læs mere om vores løsninger og serviceydelser på https://example.org Med venlig hilsen SeeSafe IT ømrervænget 6 DK-4700 Næstved Service@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Velkommen hos SeeSafe IT", "sender_email" => "Service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "83", "variabel" => "CrmSalesCounter", "vaerdi" => "1000", "beskrivelse" => "Sales Counter start Value", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "84", "variabel" => "CrmServiceCounter", "vaerdi" => "20000", "beskrivelse" => "Service Counter start Value", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "85", "variabel" => "CrmSolutionCounter", "vaerdi" => "50000", "beskrivelse" => "Solution Counter start Value", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "86", "variabel" => "IdPrefix", "vaerdi" => "CR-", "beskrivelse" => "Prefix for Title and Tag for IDPrefix for Title and Tag for ID", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "87", "variabel" => "SMTPAuthentication", "vaerdi" => "1", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "88", "variabel" => "DefaultSystemSenderName", "vaerdi" => "CRM- CloudCRM", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "23", "variabel" => "test22", "vaerdi" => "testVa", "beskrivelse" => "eererer", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "1"),
            array("id" => "30", "variabel" => "normalRowBack", "vaerdi" => "f7fafc", "beskrivelse" => "Baggrundsfarve på NormalRow", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "31", "variabel" => "normalRowColor", "vaerdi" => "000000", "beskrivelse" => "Skriftfarve på NormalRow", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "20", "variabel" => "AdminEmail", "vaerdi" => "admin@example.org", "beskrivelse" => "Admin-mail adresse. Bliver brugt ved på automatiske mails.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "22", "variabel" => "NytPWMail", "vaerdi" => "Hej XXcloud_crm_kontaktpersoner.navnXX, Hermed sender vi dig et nyt password til https://example.org Brugernavn: XXcloud_crm_kontaktpersoner.emailXX Adgangskode: OOpasswordOO Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Nyt password til https://example.org", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "26", "variabel" => "asd", "vaerdi" => "asd", "beskrivelse" => "asd", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "1"),
            array("id" => "27", "variabel" => "asd", "vaerdi" => "asd", "beskrivelse" => "asd", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "1"),
            array("id" => "28", "variabel" => "headerRowBack", "vaerdi" => "edf4f9", "beskrivelse" => "Baggrundsfarven på HeaderRows", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "29", "variabel" => "headerRowColor", "vaerdi" => "1d4375", "beskrivelse" => "Skriftfarve på HeaderRow", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "32", "variabel" => "SagsstyringURL", "vaerdi" => "http://sag.example.org", "beskrivelse" => "URL på sagsstyring", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "33", "variabel" => "BeskMaxLength", "vaerdi" => "100", "beskrivelse" => "Maksimum vist længde af en beskrivelse under firmaernes sager", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "34", "variabel" => "MaxShowCases", "vaerdi" => "10", "beskrivelse" => "Antal viste sager under sager", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "35", "variabel" => "BogholderiEmail", "vaerdi" => "invoice@example.org", "beskrivelse" => "Bogholderiets email-adresse.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "38", "variabel" => "EmailSender", "vaerdi" => "Cloud CRM", "beskrivelse" => "Afsenderen af automatiske emails", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "39", "variabel" => "kvitteringshash", "vaerdi" => "98SDgpiotywnxNXNcvmxMXMXMXMxncncnDCxdsf33333gJDJF)¤KDMCSsd3Sf4", "beskrivelse" => "hashværdi til kvitteringer", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "40", "variabel" => "kvitteringsURL", "vaerdi" => "http://www.wifi-shop.org/blank.php?p=bestilling&admin_set=1&vis_ordre=", "beskrivelse" => "url til kvitteringer", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "41", "variabel" => "SSOControlPath", "vaerdi" => "https://sso.example.org/Control", "beskrivelse" => "URL til SSO Control", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "42", "variabel" => "NotLoggedInText", "vaerdi" => "Du er ikke logget ind, og blev derfor n&aelig;gtet afgang til den p&aring;g&aelig;ldende side!", "beskrivelse" => "Fejl ved ikke-logget-ind", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),

            array("id" => "43", "variabel" => "KundekartotekURL", "vaerdi" => "http://kk.example.org", "beskrivelse" => "kundekartotekets URL", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "47", "variabel" => "cloud_crm_URL", "vaerdi" => "https://crm.example.org", "beskrivelse" => "CRM URL", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "44", "variabel" => "wlanshop_URL", "vaerdi" => "https://cms.example.org/sitemanager.php?p=forside", "beskrivelse" => "Sitemanager URL", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "48", "variabel" => "profilURL", "vaerdi" => "https://shop.example.org/", "beskrivelse" => "profilsitemanager URL", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "51", "variabel" => "serviceSagOprettet", "vaerdi" => "Hej OOcontactNameOO Tak for jeres henvendelse til SeeSafe IT. Hermed fremsendes en bekræftelse på registrering af jeres servicesag i vores system. Sagsnummer: OOidOO Vedr.: OOemneOO Vi vender tilbage, så snart vi har gennemgået jeres forespørgsel. Med venlig hilsen SeeSafe IT https://example.org", "beskrivelse" => "Servicesag #OOidOO - OOemneOO", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "57", "variabel" => "pakkeLabelAfsendt", "vaerdi" => "Hej XXnavnXX, Vi har afsendt en pakke til dig med Track & Trace nummer: XXtrack_and_traceXX Du kan følge din pakke på følgende side: http://www.postdanmark.org/da/Sider/TrackTrace.aspx?search=XXtrack_and_traceXX Med venlig hilsen SeeSafe IT https://example.org", "beskrivelse" => "Pakke afsendt", "sender_email" => "kontakt@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "52", "variabel" => "serviceSagStatusAendring", "vaerdi" => "Hej OOnavnOO, Vedr. sagsnummer: OOidOO - OOemneOO Din servicesag har fået ny status: XXstatusTypeDescriptionXX. Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved Service@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Servicesag #OOidOO status", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "89", "variabel" => "DefaultSystemSenderEmail", "vaerdi" => "crm@example.org", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "90", "variabel" => "DefaultEmail", "vaerdi" => "service1@example.org", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "91", "variabel" => "SMTPHost", "vaerdi" => "mail.smtp2go.com", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "92", "variabel" => "SMTPPort", "vaerdi" => "2525", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "93", "variabel" => "SMTPUsername", "vaerdi" => "crm@example.org", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "94", "variabel" => "SMTPPassword", "vaerdi" => "Mgp7B2kNqKffmlzB", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "95", "variabel" => "SMTPEncryption", "vaerdi" => "ssl", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "53", "variabel" => "RMAOprettet", "vaerdi" => "Kære OOcontactNameOO,Vi har modtaget din fejlmelding i vores system.Du vil blive orienteret,når vi gennemgår sagen.Ønsker du at opdatere informationer til sagen,bedes du venligst besvare denne e-mail.Sagsnr.: OOrma_idOO Service-ID: OOservice_idOO Type: XXcloud_crm_serviceaftaler.service_typeXX Serviceaftale: OOis_expiredOO Fejlbeskrivelse: OOerror_messageOO Med venlig hilsen SeeSafe IT https://example.org", "beskrivelse" => "RMA #OOrma_idOO kvittering", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "54", "variabel" => "nyBeskedRMA", "vaerdi" => "Hej XXcontact_nameXX,Der er kommet følgende besked til din RMA-sag: OOmessageOO Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "RMA #OOrma_idOO - ny besked", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "55", "variabel" => "statusAendringRMA", "vaerdi" => "Hej OOcontactNameOO", "beskrivelse" => "Din RMA-sag har ændret status til: OOnewStatusOO. Kontakt os endelig,hvis du har spørgsmål til ændringen. Med venlig hilsen SeeSafe IT https://example.org", "beskrivelse" => "RMA #OOrma_idOO - status ændret", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "56", "variabel" => "nyBeskedServicesag", "vaerdi" => "Hej OOcontact_nameOO Vedr. sagsnummer: OOidOO - OOemneOO OOmessageOO Med venlig hilsen SeeSafe IT https://example.org", "beskrivelse" => "Servicesag #OOidOO - ny besked", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "58", "variabel" => "nyBeskedRMAProdukt", "vaerdi" => "Hej OOcontact_nameOO,Der er kommet følgende besked til din RMA-sag: OOmessageOO Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "RMA #OOrma_idOO - ny besked", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "68", "variabel" => "KlippekortTilfoejetBogholder ", "vaerdi" => "Der er blevet tilføjet et nyt klippekort på en kunde. Kunde: XXcloud_crm_firmainfo.firmanavnXX Klippekort ID: CRXXcloud_crm_custklip.cr_idXX Klippekort varenummer: XXcloud_crm_klippekort.varenummerXX Klippekort navn: XXcloud_crm_klippekort.navnXX Antal klip: XXcloud_crm_custklip.antalXX Notat: XXcloud_crm_custklip.commentXX Husk at tjekke om klippekortet er faktureret,til kunden! Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Klippekort solgt", "sender_email" => "crm@example.org", "sender_display_name" => "CloudCRM", "type" => "2", "hidden" => "0"),
            array("id" => "67", "variabel" => "NyOpgaveTilAnsvarlige", "vaerdi" => "Hej OOnameOO En ny opgave er oprettet på Cloud CRM,hvor du er ansvarlig. Husk at gennemlæse opgaven i god tid. Emne: XXemneXX Du kan logge på Cloud CRM,for yderligere info. Husk at ændre sagens forløb,når du arbejder på sagen. Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Du har en ny Opgave på Cloud CRM", "sender_email" => "crm@example.org", "sender_display_name" => "CloudCRM", "type" => "2", "hidden" => "0"),
            array("id" => "63", "variabel" => "KlippekortTilfoejet", "vaerdi" => "Kære OOnavnOO, Der er netop blevet tilføjet et nyt klippekort til jeres konto. Klippekortet kan benyttes til teknisk assistance og konfigurationer. Klippekort ID: CRXXcloud_crm_custklip.cr_idXX  Beskrivelse: XXcloud_crm_klippekort.navnXX Antal klips tilføjet: XXcloud_crm_klippekort.antalXX Med venlig hilsen SeeSafe IT https://example.org", "beskrivelse" => "Nyt klippekort tilføjet - CRXXcloud_crm_custklip.cr_idXX", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "64", "variabel" => "KlippekortTrukket", "vaerdi" => "Kære OOnavnOO Der er netop blevet trukket OOklipOO på jeres klippekort. Der er dermed OOresterende_klipOO klip tilbage på jeres klippekort. Klippekortet er anvendt i forbindelse med teknisk assistance. Kontakt os venligst hvis du ønsker yderligere information. Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Status på klippekort", "sender_email" => "kontakt@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "65", "variabel" => "BogholderiSsoId", "vaerdi" => "56", "beskrivelse" => "SSO-bruger ID til bogholderiet.", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "70", "variabel" => "KontaktFormularKundeKopiService", "vaerdi" => "Hej OOnameOO Din forespørgsel angående teknisk assistance er oprettet i vores system. Virksomhedsnavn: OOcompany_nameOO CVR: OOvat_numberOO Organisations-type: OOcompany_typeOO Navn: OOnameOO Email: OOemailOO Telefonnr.: OOphoneOO Sagsnummer: #OOcase_idOO Besked: OOmessageOO Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved Service@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Teknisk assistance #OOcase_idOO", "sender_email" => "Service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "69", "variabel" => "KontaktFormularKundeKopiSalg", "vaerdi" => "Hej OOnameOO Tak for jeres henvendelse til SeeSafe IT. Vi sender hermed en bekræftelse på registrering af jeres sag i vores system. Virksomhedsnavn: OOcompany_nameOO CVR:  OOvat_numberOO Organisations-type:  OOcompany_typeOO Navn:  OOnameOO Email:  OOemailOO Telefonnr.:  OOphoneOO Sagsnummer: #OOcase_idOO Besked:  OOmessageOO Vi vender tilbage, så snart vi har gennemgået jeres forespørgsel. Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved kontakt@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Salgsrådgivning #OOcase_idOO", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "71", "variabel" => "DefaultCRMSenderMail", "vaerdi" => "kontor@example.org", "beskrivelse" => " ", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "72", "variabel" => "DefaultCRMSenderDisplayName", "vaerdi" => "kontor@example.org", "beskrivelse" => " ", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "73", "variabel" => "DefaultShopSenderMail", "vaerdi" => "kontor@example.org", "beskrivelse" => " ", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "74", "variabel" => "DefaultShopSenderDisplayName", "vaerdi" => "kontor@example.org", "beskrivelse" => " ", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "96", "variabel" => "", "vaerdi" => "", "beskrivelse" => "", "sender_email" => "", "sender_display_name" => "", "type" => "2", "hidden" => "1"),
            array("id" => "79", "variabel" => "salgsSagOprettet", "vaerdi" => "Hej OOcontactNameOO Sagsnummer: OOidOO Emne: OOemneOO Tak for jeres henvendelse til SeeSafe IT. Vi sender hermed en bekræftelse på registrering af jeres sag i vores system. Vi vender tilbage, så snart vi har gennemgået jeres forespørgsel. Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved Service@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Oprettelse af sag #OOidOO", "sender_email" => "service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "80", "variabel" => "nyBeskedSalgssag", "vaerdi" => "Hej OOcontact_nameOO OOmessageOO Med venlig hilsen SeeSafe IT Tømrervænget 6 DK-4700 Næstved Service@example.org | +45 45 66 00 00 | https://example.org", "beskrivelse" => "Vedr. #OOidOO - ny besked", "sender_email" => "Service@example.org", "sender_display_name" => "Service", "type" => "2", "hidden" => "0"),
            array("id" => "81", "variabel" => "Importer leads", "vaerdi" => "", "beskrivelse" => "", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "82", "variabel" => "APIBasePath", "vaerdi" => "/", "beskrivelse" => "Base path for API", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0"),
            array("id" => "97", "variabel" => "CompanyDomain", "vaerdi" => "company.example.org", "beskrivelse" => "company url", "sender_email" => "NULL", "sender_display_name" => "NULL", "type" => "1", "hidden" => "0")
        );
        // Insert data into the table
        try {
            // DB::connection($company_db)->table('cloud_variabler')->insert($general_settings);
            DB::connection($company_db)->table('cloud_variabler')->upsert(
                $general_settings, 
                ['id'], 
                ['variabel', 'vaerdi', 'beskrivelse', 'sender_email', 'sender_display_name', 'type', 'hidden'] 
            );
            error_log("Data inserted successfully!");
        } catch (\Exception $e) {
            error_log("Failed to insert data: " . $e->getMessage());
        }
    }
}
