<?php
/***************************************************************************
*
* @package Medals Mod for phpBB3
* @version $Id: medals.php,v 0.7.0 2008/01/14 Gremlinn$
* @copyright (c) 2008 Nathan DuPra (mods@dupra.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'IMG_ICON_POST_APPROVE'			=> 'Approva',
	'ACP_MEDALS_INDEX'				=> 'Medaglie ACP',
	'ACP_MEDALS_INDEX_EXPLAIN'		=> 'Medaglie Index Pagina',
	'ACP_MEDALS_TITLE'				=> 'Medaglie Manager',
	'ACP_MEDALS_SETTINGS'			=> 'Configurazione',

	'MEDALS_MOD_INSTALLED'			=> 'Medaglie Estensione versione %s installata',
	'MEDALS_MOD_UPDATED'			=> 'Medaglie Estensione aggiornata alla versione %s',
	'MEDALS_MOD_MANUAL'				=> 'Hai una vecchia versione di Medals Extension installata.<br />Sarà necessario disinstallare prima la vecchia versione<br />Accertarsi di effettuare copie di backup prima.',

	'ACL_U_AWARD_MEDALS'			=> 'Puoi dare medaglie agli utenti',
	'ACL_U_NOMINATE_MEDALS'			=> 'Puoi nominare utenti per le medaglie',
	'ACL_A_MANAGE_MEDALS'			=> 'Puoi usare il modulo delle medaglie',

// Medals Management
	'ACP_MEDAL_MGT_TITLE'				=> 'Medaglie Opzioni',
	'ACP_MEDAL_MGT_DESC'				=> 'Qui è possibile visualizzare, creare, modificare ed eliminare le categorie medaglia',

	'ACP_MEDALS'						=> 'Medaglie',
	'ACP_MEDALS_DESC'					=> 'Qui è possibile visualizzare, creare, modificare ed eliminare medaglie per questa categoria.',
	'ACP_MULT_TO_USER'					=> 'Numero di medaglie per utente',
	'ACP_USER_NOMINATED'				=> 'Utente nomina',
	'ACP_MEDAL_LEGEND'					=> 'Medaglia',
	'ACP_MEDAL_TITLE_EDIT'				=> 'Modifica Medaglia',
	'ACP_MEDAL_TEXT_EDIT'				=> 'Modifica una medaglia esistente',
	'ACP_MEDAL_TITLE_ADD'				=> 'Crea Medaglia',
	'ACP_MEDAL_TEXT_ADD'				=> 'Crea una nuova medaglia da zero',
	'ACP_MEDAL_DELETE_GOOD'				=> 'La medaglia è stata rimossa con successo.<br /><br /> Click <a href="%s">qui</a> per ritornare alla categoria precedente',
	'ACP_MEDAL_EDIT_GOOD'				=> 'La medaglia è stata aggiornata con successo.<br /><br /> Click <a href="%s">qui</a> per tornare alle categorie delle medaglie',
	'ACP_MEDAL_ADD_GOOD'				=> 'Medaglia aggiunta con successo.<br /><br /> Click <a href="%s">qui</a> per andare alle categorie delle medaglie',
	'ACP_CONFIRM_MSG_1'					=> 'Sei sicuro di voler cancellare questa medaglia? Saranno eliminate queste medaglia a tutti gli utenti che le possiedono. <br /><br /><form method="post"><fieldset class="submit-buttons"><input class="button1" type="submit" name="confirm" value="Yes" />&nbsp;<input type="submit" class="button2" name="cancelmedal" value="No" /></fieldset></form>',
	'ACP_NAME_TITLE'					=> 'Nome Medaglia',
	'ACP_NAME_DESC'						=> 'Descrizione Medaglia',
	'ACP_IMAGE_TITLE'					=> 'Immagine Medaglia',
	'ACP_IMAGE_EXPLAIN'					=> 'La gif immagine deve essere dentro la cartella: images/medals/ ',
	'ACP_DEVICE_TITLE'					=> 'Immagine dispositivo',
	'ACP_DEVICE_EXPLAIN'				=> 'Il nome di base della gif dentro la directory images/medaglie/ , da applicare per creare dinamicamente le medaglie.<br /> Ex. device-2.gif = device',
	'ACP_PARENT_TITLE'					=> 'Medaglia Categoria',
	'ACP_PARENT_EXPLAIN'				=> 'La categoria per la medaglia da inserire in',
	'ACP_DYNAMIC_TITLE'					=> 'Immagine medaglia dinamica',
	'ACP_DYNAMIC_EXPLAIN'				=> 'Creare dinamicamente immagini di medaglie per più premiazioni.',
	'ACP_NOMINATED_TITLE'				=> 'Medaglia Nominazione',
	'ACP_NOMINATED_EXPLAIN'				=> 'Gli utenti possono nominare altri utenti per questa medaglia?',
	'ACP_CREATE_MEDAL'					=> 'Crea medaglia',
	'ACP_NO_MEDALS'						=> 'Nessuna Medaglia',
	'ACP_NUMBER'						=> 'Numero di medaglie',
	'ACP_NUMBER_EXPLAIN'				=> 'Definisce quante volte questa medaglia può essere assegnata ad un utente.',
	'ACP_POINTS'						=> 'Punti',
	'ACP_POINTS_EXPLAIN'				=> 'Definisce come vengono assegnati i punti (o sottratti) per ricevere questa medaglia.<br />Funziona con Ultimate Points Mod.',

	'ACP_MEDALS_MGT_INDEX'				=> 'Medaglia Categorie',
	'ACP_MEDAL_TITLE_CAT'				=> 'Modifica Categoria',
	'ACP_MEDAL_TEXT_CAT'				=> 'Modifica categoria esistente',
	'ACP_MEDAL_LEGEND_CAT'				=> 'Categoria',
	'ACP_NAME_TITLE_CAT'				=> 'Nome Categoria',
	'ACP_CREATE_CAT'					=> 'Crea Categoria',
	'ACP_CAT_ADD_FAIL'					=> 'Nessun nome per questa categoria è stato aggiunto.<br /><br /> Click <a href="%s">qui</a> per tornare alla lista delle categorie',
	'ACP_CAT_ADD_GOOD'					=> 'La categoria è stata aggiunta con successo.<br /><br /> Click <a href="%s">qui</a> per tornare alla lista delle categorie',
	'ACP_CAT_EDIT_GOOD'					=> 'Categoria modificata con successo.<br /><br /> Click <a href="%s">qui</a> per tornare alla lista delle categorie',
	'ACP_CAT_DELETE_CONFIRM'			=> 'A quale categoria si desidera spostare tutte le medaglie da questa/e categorie in eliminazione? <br /><form method="post"><fieldset class="submit-buttons"><select name="newcat">%s</select><br /><br /><input class="button1" type="submit" name="moveall" value="Move All Medals" />&nbsp;<input class="button2" type="submit" name="deleteall" value="Delete All Medals" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="Cancel Deletion" /></fieldset></form>',
	'ACP_CAT_DELETE_CONFIRM_ELSE'		=> 'Non esistono altre categorie per spostare queste medaglie. <br /> Sei sicuro di voler rimuovere questa categoria e tutte le sue medaglie?<br /><form method="post"><fieldset class="submit-buttons"><br /><input class="button2" type="submit" name="deleteall" value="Yes" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="No" /></fieldset></form>',
	'ACP_CAT_DELETE_GOOD'				=> 'In questa categoria, tutto il suo contenuto sono stati cancellati con successo.<br /><br /> Click <a href="%s">qui</a> per tornare alla lista delle categorie',
	'ACP_CAT_DELETE_MOVE_GOOD'			=> 'Tutte le medaglie da "%1$s" sono state spostate in "%2$s" e le categorie sono state cancellate con successo..<br /><br /> Click <a href="%3$s">qui</a> per tornare alla lista delle categorie',
	'ACP_NO_CAT_ID'						=> 'Nennuna categoria',

// Medals Configuration
	'ACP_CONFIG_TITLE'					=> 'Configurazione Medaglie',
	'ACP_CONFIG_DESC'					=> 'Qui è possibile impostare le opzioni per la Medal System 0.21.0',
	'ACP_MEDALS_CONF_SETTINGS'			=> 'Medaglie Configurazione Opzioni',
	'ACP_MEDALS_CONF_SAVED'				=> 'Configurazione medaglie salvato<br /><br /> Click <a href="%s">qui</a> per tornare ad ACP Configurazione',
	'ACP_MEDALS_SM_IMG_WIDTH'			=> 'Larghezza per le medaglie piccole',
	'ACP_MEDALS_SM_IMG_WIDTH_EXPLAIN'	=> 'La larghezza (in pixels) delle medaglie da visualizzare in viewtopic e nel profilo utenti.<br />Seleziona 0 per non definire la larghezza.',
	'ACP_MEDALS_SM_IMG_HT'				=> 'Altezza medaglie piccole',
	'ACP_MEDALS_SM_IMG_HT_EXPLAIN'		=> 'Altezza (in pixels) per le medaglie da visualizzare in viewtopic e nel profilo utenti.<br />Seleziona 0 per non definire altezza.',
	'ACP_MEDALS_VT_SETTINGS'			=> 'Viewtopic visualizzazione opzioni',
	'ACP_MEDALS_TOPIC_DISPLAY'			=> 'Aggiungi visualizzazione medaglie in Viewtopic',
	'ACP_MEDALS_TOPIC_ROW'				=> 'Numero di medaglie',
	'ACP_MEDALS_TOPIC_ROW_EXPLAIN'		=> 'Numero di medaglie da visualizzare in Viewtopic.',
	'ACP_MEDALS_TOPIC_COL'				=> 'Numero di medaglie sotto',
	'ACP_MEDALS_TOPIC_COL_EXPLAIN'		=> 'Numero di medaglie da visualizzare in Viewtopic sotto.',
	'ACP_MEDALS_PROFILE_ACROSS'			=> 'Numero di medaglie da visualizzare nel profilo',
	'ACP_MEDALS_PROFILE_ACROSS_EXPLAIN'	=> 'Numero di medaglie da visualizzare nel profilo informazione medaglie.',
	'ACP_MEDALS_ACTIVATE' 				=> 'Estensione Medaglie Attivata',
));
