<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Novalnet payment plugin
 *
 * Strings for component 'paygw_novalnet', language 'en'
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License
 * that is bundled with this package in the file freeware_license_agreement.txt
 *
 * DISCLAIMER
 *
 * If you wish to customize Novalnet payment extension for your needs, please contact technic@novalnet.de for more information.
 *
 * @package    paygw_novalnet
 * @copyright  2025 Novalnet <technic@novalnet.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['gatewaydescription'] = 'Das Novalnet Gateway ist eine End-to-End-Lösung für die Full-Service-Verarbeitung von internationalen und lokalen Zahlungen auf der ganzen Welt. Durch die Anbindung an die Novalnet-Plattform stellt es ein Höchstmaß an Sicherheit für Ihre Transaktionen dar.';
$string['gatewayname'] = 'Novalnet Payments (150+ Zahlungsmethoden weltweit)';
$string['internalerror'] = 'Es ist ein interner Fehler aufgetreten. Bitte kontaktieren Sie uns.';
$string['pluginname'] = 'Novalnet';
$string['api_config_heading'] = 'Novalnet API-Konfiguration';
$string['pluginname_desc'] = '<p>Das Novalnet Payment Plugin für Moodle ist eine End-to-End-Lösung für die Full-Service-Verarbeitung von internationalen und lokalen Zahlungen auf der ganzen Welt. Durch die Anbindung an die Novalnet-Plattform stellt es ein Höchstmaß an Sicherheit für Ihre Transaktionen dar. Zu diesem Zweck wird die Novalnet jährlich in einem umfassenden Sicherheits-Audit sowohl von der BaFin als auch nach dem Payment Card Industry Data Security Standard (PCI DSS) auf der höchsten Stufe 1 geprüft und zertifiziert. <br />Für die Einrichtung und Verwendung des Plugins finden Sie die Installationsanleitung <a href="https://www.novalnet.com/docs/plugins/installation-guides/moodle-installation-guide.pdf" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Hier</a></p>';
$string['novalnet_required_error'] = 'Bitte geben Sie die erforderlichen Informationen in das Feld ein.';
$string['novalnet_trans_comments'] = 'Zahlungshinweis:';
$string['novalnet_key_password'] = 'Zahlungs-Zugriffsschlüssel';
$string['novalnet_public_key'] = 'Produktaktivierungsschlüssel';
$string['novalnet_public_key_help'] = 'Ihr Produktaktivierungsschlüssel ist ein eindeutiges Token für die Händlerauthentifizierung und die Zahlungsabwicklung. ';
$string['novalnet_key_password_help'] = 'Ihr geheimer Schlüssel zur Verschlüsselung der Daten, um Manipulation und Betrug zu vermeiden. ';
$string['novalnet_tariff_id'] = 'Auswahl der Tarif-ID';
$string['novalnet_active_payments'] = 'Zahlungsarten';
$string['novalnet_active_payments_help'] = 'Die ausgewählten Zahlungsarten werden auf der Seite für die Zahlungsanmeldung angezeigt.';
$string['novalnet_tariff_id_help'] = 'Wählen Sie eine Tarif-ID, die dem bevorzugten Tarifplan entspricht, den Sie im <a href="https://admin.novalnet.de" target="_blank">Novalnet Admin-Portal</a> für dieses Projekt erstellt haben ';
$string['live'] = 'Live';
$string['novalnet_test_mode'] = 'Testmodus aktivieren';
$string['novalnet_test_mode_help'] = 'Die Zahlung wird im Testmodus durchgeführt, daher wird der Betrag für diese Transaktion nicht eingezogen';
$string['sandbox'] = 'Sandkasten';
$string['novalnet_vendor_script_heading'] = 'Benachrichtigungs- / Webhook-URL festlegen';
$string['novalnet_webhook_configure'] = 'Konfigurieren';
$string['novalnet_webhook_url'] = 'Benachrichtigungs- / Webhook-URL festlegen';
$string['novalnet_webhook_url_help'] = 'Sie müssen die folgende Webhook-URL im <a href="https://admin.novalnet.de" target="_blank">Novalnet Admin-Portal</a> hinzufügen. Dadurch können Sie Benachrichtigungen über den Transaktionsstatus erhalten ';
$string['novalnet_callback_test_mode'] = 'Allow manual testing of the Notification / Webhook URL';
$string['novalnet_webhook_configure_success'] = 'Callbackskript-/ Webhook-URL wurde erfolgreich im Novalnet Admin Portal konfiguriert';
$string['novalnet_webhook_notice'] = 'Sind Sie sicher, dass Sie die Webhook-URL im Novalnet Admin Portal konfigurieren möchten?';
$string['novalnet_webhook_url_error'] = 'Bitte geben Sie eine gültige Webhook-URL ein';
$string['novalnet_callback_emailtoaddr'] = 'E-Mails senden an';
$string['novalnet_callback_test_mode_help'] = 'Aktivieren Sie diese Option, um die Novalnet-Benachrichtigungs-/Webhook-URL manuell zu testen. Deaktivieren Sie die Option, bevor Sie Ihren Shop liveschalten, um unautorisierte Zugriffe von Dritten zu blockieren.';
$string['novalnet_callback_emailtoaddr_help'] = 'Meldungen zur Ausführung von Benachrichtigungen/Webhook-URLs werden an diese E-Mail gesendet. Hinweis: Um mehrere E-Mails zu konfigurieren, trennen Sie diese durch ein Komma (",").';
$string['novalnet_enforce_3d'] = '3D-Secure-Zahlungen außerhalb der EU erzwingen';
$string['novalnet_enforce_3d_help'] = 'Wenn Sie diese Option aktivieren, werden alle Zahlungen mit Karten, die außerhalb der EU ausgegeben wurden, mit der starken Kundenauthentifizierung (Strong Customer Authentication, SCA) von 3D-Secure 2.0 authentifiziert.';
$string['novalnet_payment_action'] = 'Aktion für vom Besteller autorisierte Zahlungen';
$string['novalnet_payment_action_help'] = 'Wählen Sie, ob die Zahlung sofort belastet werden soll oder nicht. Zahlung einziehen: Betrag sofort belasten. Zahlung autorisieren: Die Zahlung wird überprüft und autorisiert, aber erst zu einem späteren Zeitpunkt belastet. So haben Sie Zeit, über die Bestellung zu entscheiden.';
$string['novalnet_payment_capture'] = 'Zahlung einziehen';
$string['novalnet_payment_authorize'] = 'Zahlung autorisieren';
$string['novalnet_payment_zero_amount_booking'] = 'Mit Nullbetrag autorisieren';
$string['novalnet_payment_authorize_limit'] = 'Mindesttransaktionsbetrag für die Autorisierung';
$string['novalnet_payment_authorize_limit_help'] = 'Übersteigt der Bestellbetrag das genannte Limit, wird die Transaktion, bis zu ihrer Bestätigung durch Sie, auf on hold gesetzt. Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.';
$string['novalnet_due_date'] = 'Fälligkeitsdatum (in Tagen)';
$string['novalnet_invoice_due_date'] = 'Fälligkeitsdatum (in Tagen)';
$string['novalnet_prepayment_due_date'] = 'Fälligkeitsdatum (in Tagen)';
$string['novalnet_due_date_help'] = 'Geben Sie die Anzahl der Tage ein, nach denen der Zahlungsbetrag eingezogen werden soll (muss zwischen 3 und 14 Tagen liegen).';
$string['novalnet_invoice_due_date_help'] = 'Anzahl der Tage, die der Käufer Zeit hat, um den Betrag an Novalnet zu überweisen (muss mehr als 7 Tage betragen). Wenn Sie dieses Feld leer lassen, werden standardmäßig 14 Tage als Fälligkeitsdatum festgelegt';
$string['novalnet_prepayment_due_date_help'] = 'Anzahl der Tage, die der Käufer Zeit hat, um den Betrag an Novalnet zu überweisen (muss zwischen 7 und 28 Tagen liegen). Wenn Sie dieses Feld leer lassen, werden standardmäßig 14 Tage als Fälligkeitsdatum festgelegt..';
$string['novalnet_cashpayment_due_date'] = 'Verfallsdatum des Zahlscheins (in Tagen)';
$string['novalnet_cashpayment_due_date_help'] = 'Anzahl der Tage, die der Käufer Zeit hat, um den Betrag in einer Filiale zu bezahlen. Wenn Sie dieses Feld leer lassen, ist der Zahlschein standardmäßig 14 Tage lang gültig.';
$string['force_normal_payment'] = 'Zahlung ohne Zahlungsgarantie erzwingen';
$string['force_normal_payment_help'] = 'Falls die Zahlungsgarantie zwar aktiviert ist, jedoch die Voraussetzungen für Zahlungsgarantie nicht erfüllt sind, wird die Zahlung ohne Zahlungsgarantie verarbeitet. Die Voraussetzungen finden Sie in der Installationsanleitung unter "Zahlungsgarantie aktivieren".';
$string['instalment_cycles'] = 'Anzahl der Raten';
$string['instalment_cycles_help'] = 'Wählen Sie die Ratenzahlungszyklen, die im Ratenzahlungsplan in Anspruch genommen werden können';
$string['instalment_cycles_text'] = '{$a} Zyklen';
$string['novalnet_guarantee_allow_b2b'] = 'B2B-Kunden erlauben';
$string['novalnet_guarantee_allow_b2b_help'] = 'Erlauben Sie B2B-Kunden, eine Bestellung aufzugeben. <br/><br/><b>Hinweis: </b><br/>Um eine garantierte Zahlung für einen B2B-Kunden zu ermöglichen, sollten Sie ein Firmenfeld erstellen (mit dem technischen Namen "Firma"), das während des Registrierungsprozesses des Benutzers angezeigt wird.';
$string['CREDITCARD'] = 'Kredit- / Debitkarte';
$string['CREDITCARD_DESCRIPTION'] = 'Der Betrag wird von Ihrer Kreditkarte abgebucht, sobald die Bestellung abgeschickt wird.';
$string['DIRECT_DEBIT_SEPA'] = 'SEPA-Lastschrift';
$string['DIRECT_DEBIT_SEPA_DESCRIPTION'] = 'Ihr Konto wird nach Abschicken der Bestellung belastet.';
$string['GUARANTEED_DIRECT_DEBIT_SEPA'] = 'SEPA-Lastschrift mit Zahlungsgarantie';
$string['INSTALMENT_DIRECT_DEBIT_SEPA'] = 'Ratenzahlung per SEPA-Lastschrift';
$string['INVOICE'] = 'Kauf auf Rechnung';
$string['INVOICE_DESCRIPTION'] = 'Zahlen Sie sicher und bequem nach Erhalt der Ware.';
$string['GUARANTEED_INVOICE'] = 'Rechnung mit Zahlungsgarantie';
$string['INSTALMENT_INVOICE'] = 'Ratenzahlung per Rechnung';
$string['PREPAYMENT'] = 'Vorkasse';
$string['PREPAYMENT_DESCRIPTION'] = 'Bezahlen Sie im Voraus auf das Bankkonto, sobald die Bestellung bestätigt ist.';
$string['CASHPAYMENT'] = 'Barzahlen/viacash';
$string['CASHPAYMENT_DESCRIPTION'] = 'Mit Abschluss der Bestellung bekommen Sie einen Zahlschein angezeigt, den Sie sich ausdrucken oder auf Ihr Handy schicken lassen können. Bezahlen Sie den Online-Einkauf mit Hilfe des Zahlscheins an der Kasse einer Barzahlen-Partnerfiliale.';
$string['ONLINE_TRANSFER'] = 'Sofortüberweisung';
$string['ONLINE_TRANSFER_DESCRIPTION'] = 'Bezahlen Sie einfach, schnell und sicher per Online-Überweisung.';
$string['IDEAL'] = 'iDEAL';
$string['GIROPAY'] = 'giropay';
$string['GIROPAY_DESCRIPTION'] = 'Bezahlen Sie einfach, schnell und sicher per Online-Überweisung (PIN & TAN erforderlich).';
$string['EPS'] = 'eps';
$string['PAYPAL'] = 'PayPal';
$string['PAYPAL_DESCRIPTION'] = 'Sie werden auf die abgesicherte PayPal Seite weitergeleitet, um die Zahlung abzuschließen.';
$string['PRZELEWY24'] = 'Przelewy24';
$string['POSTFINANCE'] = 'PostFinance E-Finance';
$string['POSTFINANCE_DESCRIPTION'] = 'Bezahlen Sie mit Ihrem PostFinance E-Finance einfach und sicher online';
$string['POSTFINANCE_CARD'] = 'PostFinance Card';
$string['POSTFINANCE_CARD_DESCRIPTION'] = 'Mit der PostFinance Card einfach und sicher online bezahlen';
$string['MULTIBANCO'] = 'Multibanco';
$string['MULTIBANCO_DESCRIPTION'] = 'Nach Abschluss Ihrer Bestellung wird Ihnen im Shop / auf der Webseite eine Zahlungsreferenz angezeigt. Mit dieser Zahlungsreferenz können Sie entweder am Multibanco-Automaten oder per Onlinebanking bezahlen.';
$string['BANCONTACT'] = 'Bancontact';
$string['BANCONTACT_DESCRIPTION'] = 'Nach der erfolgreichen Überprüfung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen.';
$string['APPLEPAY'] = 'Apple Pay';
$string['APPLEPAY_DESCRIPTION'] = 'Der Betrag wird nach erfolgreicher Authentifizierung von Ihrer Karte abgebucht.';
$string['GOOGLEPAY'] = 'Google Pay';
$string['GOOGLEPAY_DESCRIPTION'] = 'Der Betrag wird nach erfolgreicher Authentifizierung von Ihrer Karte abgebucht.';
$string['TRUSTLY'] = 'Trustly';
$string['ALIPAY'] = 'AliPay';
$string['ALIPAY_DESCRIPTION'] = 'Bezahlen mit Alipay und sicher online';
$string['WECHATPAY'] = 'WeChatPay';
$string['WECHATPAY_DESCRIPTION'] = 'Bezahlen mit WeChat Pay und sicher online';
$string['ONLINE_BANK_TRANSFER'] = 'Onlineüberweisung';
$string['ONLINE_BANK_TRANSFER_DESCRIPTION'] = ' Mit wenigen Klicks über Ihr Online-Banking ausgeführt';
$string['ONLINE_BANK_TRANSFER_DESCRIPTION_1'] = ' Einfache und sichere Überweisung';
$string['DIRECT_DEBIT_ACH'] = 'Lastschrift ACH';
$string['DIRECT_DEBIT_ACH_DESCRIPTION'] = 'Ihr Konto wird nach Abschicken der Bestellung belastet.';
$string['MBWAY'] = 'MB Way';
$string['MBWAY_DESCRIPTION'] = 'Nach Abschluss Ihrer Bestellung wird eine Zahlungsaufforderung an Ihr Mobilgerät gesendet. Sie können die PIN eingeben und die Zahlung autorisieren.';
$string['BLIK'] = 'Blik';
$string['PAYCONIQ'] = 'Payconiq';
$string['CASH_ON_DELIVERY'] = 'Barzahlung bei Abholung';
$string['CASH_ON_DELIVERY_DESCRIPTION'] = 'Beim Käufer abholen und in Bar bezahlen.';
$string['TWINT'] = 'TWINT';
$string['TWINT_DESCRIPTION'] = 'Bezahlen Sie sicher von Ihrem mobilen Gerät mit der TWINT-App';
$string['startpayment'] = 'Beginn der Zahlung';
$string['novalnet_payment_error'] = 'Die Zahlung war nicht erfolgreich. Ein Fehler trat auf.';
$string['err:assert:paymentrecord'] = 'Ungültige Anfrage: Zahlungsdatensatz nicht gefunden';
$string['err:assert:paymentrecordvariables'] = 'Ungültige Anfrage: eine oder mehrere Variablen des Zahlungsdatensatzes stimmen nicht mit der vorgesehenen Komponente, dem Zahlungsbereich oder der itemid überein';
$string['err:validatetransaction:component'] = 'Transaktion ungültig: Komponente stimmt nicht überein';
$string['err:validatetransaction:paymentarea'] = 'Transaktion ungültig: Zahlungsbereich stimmt nicht überein';
$string['err:validatetransaction:itemid'] = 'Transaktion ungültig: itemid mismatch';
$string['err:validatetransaction:userid'] = 'Transaktion ungültig: Benutzerübereinstimmung';
$string['instalmentheading'] = 'Zusammenfassung der Ratenzahlung: ';
$string['instalmentsno'] = 'S.Nr';
$string['instalmenttid'] = 'Novalnet-Transaktions-ID';
$string['instalmentamount'] = 'Betrag';
$string['instalmentnextdate'] = 'Nächste Rate fällig am';
$string['instalmentstatus'] = 'Status';
$string['cancelled'] = 'Gekündigt';
$string['pending'] = 'Ausstehend';
$string['completed'] = 'Abgeschlossen';
$string['refunded'] = 'Erstattet';
$string['selectpaymentmethod'] = 'Wählen Sie eine Zahlungsart, um Ihre Zahlung sicher abzuschließen';
$string['course_description'] = 'Kursname: {$a->coursename} & Kursgebühr: {$a->coursefee}';
$string['err:nopaymentmethods'] = 'Sie haben keine Zahlungsmethode für Novalnet aktiviert.';
$string['redirect-notify'] = 'Wenn Sie den Bezahlprozess starten, werden Sie auf die sichere Seite von Novalnet weitergeleitet, um Ihre Zahlung abzuschließen.';
$string['startpayment:failed:nopayment'] = 'Bitte wählen Sie eine Zahlungsmethode.';
$string['startpayment:failed:title'] = 'Die Zahlung konnte nicht gestartet werden.';
$string['startpayment:failed:btncancel'] = 'Schließen Sie';
$string['payment:returnpage'] = 'Status der Zahlungsabwicklung.';
$string['unknownerror'] = 'Es ist ein unbekannter Fehler aufgetreten. Bitte kontaktieren Sie den Systemadministrator.';
$string['specific_course_comment'] = 'Kommentare zur Bestellung: {$a}';
$string['wallet_card_info'] = 'Ihre Bestellung wurde erfolgreich mit Google Pay durchgeführt {$a}';
$string['novalnet_payment_name'] = 'Name der Zahlungsart: {$a}';
$string['novalnet_transaction_id'] = 'Novalnet-Transaktions-ID: {$a}';
$string['test_order_text'] = 'Testbestellung';
$string['guarantee_pending_text'] = 'Ihre Bestellung wird überprüft. Nach der Bestätigung senden wir Ihnen unsere Bankverbindung, an die Sie bitte den Gesamtbetrag der Bestellung überweisen. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann';
$string['sepa_guarantee_pending_text'] = 'Ihre Bestellung wird derzeit überprüft. Wir werden Sie in Kürze über den Bestellstatus informieren. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann.';
$string['invoice_payment_bank_text'] = 'Bitte überweisen Sie den Betrag von {$a}';
$string['instalment_payment_bank_text'] = 'Bitte überweisen Sie den Ratenzahlungsbetrag von {$a}';
$string['bank_with_due_date_text'] = ' spätestens bis zum folgenden Konto {$a}';
$string['bank_without_due_date_text'] = ' auf das folgende Konto.';
$string['account_owner'] = 'Kontoinhaber: {$a}';
$string['bank_name'] = 'Bank: {$a}';
$string['bank_place'] = 'Ort: {$a}';
$string['bank_iban'] = 'IBAN: {$a}';
$string['bank_bic'] = 'BIC: {$a}';
$string['multiple_reference_text'] = 'Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke für die Überweisung. Nur so kann Ihr Geldeingang Ihrer Bestellung zugeordnet werden.';
$string['reference_text1'] = 'Verwendungszweck 1: {$a}';
$string['reference_text2'] = 'Verwendungszweck 2: {$a}';
$string['slip_expiry_date'] = 'Verfallsdatum des Zahlscheins (in Tagen): {$a}';
$string['cash_payment_stores'] = 'Barzahlen-Partnerfilialen in Ihrer Nähe: ';
$string['multibanco_reference_text'] = 'Bitte verwenden Sie die folgende Zahlungsreferenz, um den Betrag von {$a} an einem Multibanco-Geldautomaten oder über Ihr Onlinebanking zu bezahlen.';
$string['multibanco_partner_reference'] = 'Partner-Zahlungsreferenz: {$a}';
$string['multibanco_entity_reference'] = 'Entität: {$a}';
$string['hash_check_failed_error'] = 'Während der Umleitung wurden einige Daten geändert. Die Hash-Prüfung ist fehlgeschlagen';
$string['payment:successful:subject'] = 'Zahlungsbestätigung und Registrierung erfolgreich';
$string['payment:successful:content'] = '<br /><br />Wir freuen uns, Ihnen bestätigen zu können, dass Ihre Zahlung für den Kurs {$a->course} erfolgreich eingegangen ist. Sie sind nun offiziell eingeschrieben und können Ihre Lernreise jederzeit beginnen.<br /><br />Um loszulegen, loggen Sie sich bitte in Ihr Konto ein und rufen Sie Ihren Kurs hier auf - <a href="{$a->url}" target="_blank"> {$a->url} </a>.';
$string['payment:successful:message'] = 'Ihre Zahlung war erfolgreich';
$string['payment:pending:subject'] = 'Immatrikulation ausstehend - Ihre Zahlung ist noch nicht angekommen';
$string['payment:pending:content'] = '<br /><br />Vielen Dank für Ihre Anmeldung zum Kurs - {$a} <br />
Wir möchten Sie darüber informieren, dass Ihre Zahlung derzeit noch nicht eingegangen ist. Ihre Einschreibung wird bestätigt, sobald die Zahlung erfolgreich bearbeitet wurde.<br /> Nach der Bestätigung sind Sie offiziell eingeschrieben und können Ihren Kurs sofort starten.';
$string['payment:pending:message'] = 'Die Zahlung steht noch aus, und die Anmeldung wird abgeschlossen, sobald Ihre Zahlung eingegangen ist.';
$string['payment:authorize:subject'] = 'Zahlung autorisiert - Anmeldebestätigung folgt in Kürze';
$string['payment:authorize:content'] = '<br /><br />Vielen Dank für Ihre Anmeldung zum Kurs - {$a}<br />
Wir möchten Sie darüber informieren, dass Ihre Zahlung genehmigt wurde. <br />Sobald die Zahlung erfolgreich verbucht wurde, ist Ihre Anmeldung abgeschlossen und Sie können mit dem Lernen beginnen.';
$string['payment:authorize:message'] = 'Ihre Zahlung wurde autorisiert. Sobald die Zahlung erfolgreich verbucht wurde, ist Ihre Anmeldung abgeschlossen.';
$string['payment:failed:subject'] = 'Zahlung fehlgeschlagen - Ihre Einschreibung konnte nicht abgeschlossen werden';
$string['payment:failed:content'] = '<br /><br />Wir bedauern, Ihnen mitteilen zu müssen, dass Ihre letzte Zahlung nicht bearbeitet werden konnte und aus folgendem Grund storniert wurde: {$a}.<br />
Daher konnte Ihre Anmeldung nicht abgeschlossen werden. Bitte überprüfen Sie die nachstehenden Angaben und versuchen Sie es erneut. Wenn Sie glauben, dass es sich um einen Fehler handelt oder Sie Hilfe benötigen, wenden Sie sich bitte an den Händlersupport.<br />Wir sind hier, um Ihnen zu helfen und sicherzustellen, dass Ihr Anmeldeverfahren reibungslos verläuft.<br />';
$string['payment:failed:message'] = 'Ihre Zahlung wurde storniert aufgrund von : {$a}';
$string['payment:cannotprocessstatus:subject'] = 'Fehler im Zahlungsstatus';
$string['payment:cannotprocessstatus:content'] = 'Ihre Zahlung hat einen Status, den wir (noch) nicht bearbeiten können. Bitte kontaktieren Sie den Systemadministrator.';
$string['payment:cannotprocessstatus:message'] = 'Ihre Zahlung hat einen Status, den wir (noch) nicht bearbeiten können. Bitte kontaktieren Sie den Systemadministrator.';
$string['admin:onhold:subject'] = 'Zahlungsermächtigung für die Kurseinschreibung';
$string['admin:onhold:message'] = 'Wir freuen uns, Ihnen mitteilen zu können, dass eine Zahlung von ({$a->amount}) für den Kurs ({$a->coursename}),
die mit der Enrollment ID verbunden sind: ({$a->enrolid}), erfolgreich autorisiert worden ist.<br /><br />
Die Transaktions-ID ({$a->tid}) für diese Zahlung ist {$a->paymentname}.
<br />Bitte bestätigen Sie die Zahlung oder ergreifen Sie alle notwendigen Maßnahmen zur Stornierung über die <a href="https://admin.novalnet.de" target="_blank">Novalnet Verwaltungsportal</a>, nach Bedarf.';
$string['mail:user:details'] = 'Sehr geehrter Herr/Frau {$a},';
$string['mail:merchant:support'] = 'Wenn Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an den Händlersupport.';
$string['mail:payment:details'] = '<p> Kurs & Zahlungsdetails: </p><ul><li>Kursname: <b>{$a->course}</b></li>
  <li>Betrag:  <b>{$a->amount}</b></li>
  <li>Bezahlung: <b>{$a->payment}</b></li>
  <li>Zahlungsdatum: <b>{$a->date}</b></li>
  <li>Transaktions-ID: <b>{$a->tid}</b></li>
  <li>Anmerkung zur Transaktion: </b><b>{$a->comments}</b></li>
</ul>';
$string['mail:admin:details'] = '<br /><br /><br />---------------------<br />Herzliche Grüße,<br />{$a}';
$string['messageprovider:payment_success'] = 'Zahlungsbestätigung erfolgreich erhalten - Benachrichtigung verzögert';
$string['messageprovider:payment_pending'] = 'Ihre Zahlung ist noch ausstehend - Benachrichtigung verzögert';
$string['messageprovider:payment_authorize'] = 'Ihre Zahlung wurde autorisiert - Benachrichtigung verzögert';
$string['messageprovider:payment_failed'] = 'Ihre Zahlung konnte nicht verarbeitet werden - Benachrichtigung verzögert';
$string['messageprovider:payment_cannotprocessstatus'] = 'Es gab einen Fehler bei Ihrem Zahlungsstatus - Benachrichtigung verzögert';
$string['novalnet_callback_mail_subject'] = 'Novalnet Callback Script Zugriffsbericht - Moodle';
$string['novalnet_amount_capture'] = 'Die Buchung wurde am DD-MM-YYYY um {$a}';
$string['novalnet_deactivated_message'] = 'Die Transaktion wurde am {$a} Uhr storniert';
$string['novalnet_refund_message'] = 'Die Rückerstattung für die TID {$a->ptid} mit dem Betrag {$a->amount} wurde veranlasst.';
$string['novalnet_refund_tid_message'] = ' Die neue TID für den erstatteten Betrag lautet: {$a}.';
$string['novalnet_callback_redirect_update_message'] = 'Transaktion mit TID {$a->tid} und Betrag {$a->amount} wurde am {$a->date} erfolgreich aktualisiert';
$string['novalnet_callback_cashpayment_message'] = ' Die Transaktion wurde aktualisiert. Neuer Betrag: {$a->amount}, neues Fälligkeitsdatum des Zahlscheins: {$a->date}';
$string['novalnet_callback_duedate_update_message'] = ' Die Transaktion wurde mit dem Betrag: {$a->amount} und dem Fälligkeitsdatum aktualisiert {$a->date}.';
$string['novalnet_callback_update_onhold_message'] = 'Der Status der Transaktion mit der TID: : {$a->tid} wurde am {$a->date} Uhr von ausstehend auf ausgesetzt geändert. ';
$string['novalnet_callback_credit_message'] = 'Die Gutschrift für die TID ist erfolgreich eingegangen: {$a->parent_tid} mit Betrag {$a->amount} am {$a->date}. Bitte entnehmen Sie die TID den Einzelheiten der Bestellung bei BEZAHLT in unserem Novalnet Admin-Portal: {$a->tid}. ';
$string['novalnet_credit_overpaid_message'] = 'Der Betrag wurde zu viel gezahlt.';
$string['novalnet_chargeback_message'] = 'Chargeback erfolgreich importiert für die TID: {$a->ptid} Betrag: {$a->amount} am {$a->date} Uhr. TID der Folgebuchung: {$a->tid}';
$string['novalnet_payment_reminder_message'] = 'Die Zahlungserinnerung {$a} wurde an den Kunden gesendet.';
$string['novalnet_collection_agency_message'] = 'Die Transaktion wurde an das Inkassobüro übermittelt. Inkassoreferenz: {$a}';
$string['novalnet_callback_instalment_prepaid_message'] = 'Für die Transaktions-ID ist eine neue Rate eingegangen: {$a->ptid} . Die Transaktions-ID der neuen Rate lautet: {$a->tid} mit Betrag {$a->amount} am {$a->date}.';
$string['novalnet_callback_instalment_stopped_message'] = 'Die Ratenzahlung für die TID wurde gestoppt: {$a->ptid} um {$a->date}.';
$string['novalnet_callback_instalment_refund_message'] = 'Die Rückerstattung mit dem Betrag {$a} wurde veranlasst.';
$string['novalnet_callback_instalment_cancelled_message'] = 'Die Ratenzahlung für die TID wurde gekündigt: {$a->ptid} am {$a->date}.';
$string['novalnet_callback_already_paid'] = 'Novalnet Webhook empfangen. Bestellung bereits bezahlt.';
$string['novalnet_callback_tid_existed'] = 'Novalnet Callback ausgeführt. Die Transaktions-ID existierte bereits.';
$string['novalnet_callback_unhandled_event'] = 'Die Webhook-Benachrichtigung wurde für den unbehandelten EVENT-Typ ($a) empfangen.';
$string['novalnet_callback_script_executed'] = 'Novalnet-Rückruf erhalten. Callback Script bereits ausgeführt.';
$string['novalnet_callback_status_invalid'] = 'Novalnet-Rückruf erhalten. Status ist nicht gültig.';
$string['novalnet_callback_missing_necessary_parameter'] = 'Ein notwendiger Parameter fehlt in der Anfrage.';
$string['novalnet_callback_not_json_format'] = 'Empfangene Daten sind nicht im JSON-Format $a';
$string['novalnet_callback_unauthorised_ip'] = 'Unerlaubter Zugriff von der IP $a';
$string['novalnet_callback_host_recieved_ip_empty'] = 'Unerlaubter Zugriff von der IP. Host/empfangene IP ist leer.';
$string['novalnet_callback_host_empty'] = 'Unerlaubter Zugriff von der IP. Novalnet Hostname ist leer.';
$string['novalnet_callback_missing_category'] = 'Erforderlicher Parameter Kategorie($a) nicht erhalten.';
$string['novalnet_callback_missing_parameter_category'] = 'Erforderlicher Parameter($a->parameter) in der Kategorie($a->category) nicht erhalten.';
$string['novalnet_callback_missing_tid_category'] = 'Ungültige TID in der Kategorie ($a->category) nicht empfangen $a->parameter';
$string['novalnet_callback_hash_check_failed'] = 'Bei der Benachrichtigung wurden einige Daten geändert. Die Hash-Prüfung ist fehlgeschlagen.';
$string['novalnet_callback_already_handled_shop'] = 'Der Vorgang wurde bereits in der Werkstatt bearbeitet.';
$string['novalnet_callback_reference_not_matching'] = 'Die Bestellnummer stimmt nicht überein.';
$string['novalnet_callback_reference_not_found_shop'] = 'Bestellnummer nicht im Shop gefunden..';
$string['novalnet_callback_reference_empty'] = 'Die Referenz ist leer, so dass der Auftrag nicht zugeordnet werden kann.';
$string['privacy:metadata:paygw_novalnet_transaction_detail'] = 'Speichert Transaktionsdetails im Zusammenhang mit Novalnet-Zahlungen.';
$string['privacy:metadata:paygw_novalnet_transaction_detail:userid'] = 'Die ID des Benutzers, der mit der Transaktion verbunden ist.';
$string['privacy:metadata:novalnet'] = 'Gibt die erforderlichen Benutzerdaten für die Zahlungsabwicklung an Novalnet weiter.';
$string['privacy:metadata:novalnet:first_name'] = 'Vorname des Benutzers, der eine Transaktion anfordert.';
$string['privacy:metadata:novalnet:last_name'] = 'Der Nachname des Benutzers, der eine Transaktion anfordert.';
$string['privacy:metadata:novalnet:email'] = 'E-Mail des Benutzers, der eine Transaktion beantragt.';
$string['privacy:metadata:novalnet:customer_ip'] = 'Die IP-Adresse des Benutzers, die an Novalnet gesendet wurde.';
$string['privacy:metadata:novalnet:customer_no'] = 'Die Benutzer-ID, die sich auf die an Novalnet gesendete Transaktion bezieht.';
$string['privacy:metadata:novalnet:tel'] = 'Die an Novalnet gesendete Telefonnummer des Benutzers.';
$string['privacy:metadata:novalnet:mobile'] = 'Die an Novalnet gesendete Handynummer des Benutzers.';
$string['privacy:metadata:novalnet:gender'] = 'Das Geschlecht des an Novalnet gesendeten Benutzers.';
$string['privacy:metadata:novalnet:birth_date'] = 'Das Geburtsdatum des Benutzers, das an Novalnet gesendet wird.';
$string['privacy:metadata:novalnet:billing'] = 'Die an Novalnet gesendeten Rechnungsdaten des Nutzers (einschließlich Firmenname, Adresse und andere Details).';
