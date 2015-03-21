<?php
/***************************************************************************
*
* @package Medals Mod for phpBB3
* @version $Id: medals.php,v 0.7.0 2008/01/23 Gremlinn$
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
// pms
	'PM_MESSAGE'					=> '%1$s' . "\n" . '[b]Ti è stata assegnata la medaglia "%2$s" da %3$s.' . "\n" . '%3$s che ha inviato anche il seguente messaggio:[/b]' . "\n\n",
	'PM_MESSAGE_POINTS_EARN'		=> '<br />Hai guadagnato %1$s punti %2$s.' . "\n\n",
	'PM_MESSAGE_POINTS_DEDUCT'		=> '<br />%1$s punti %2$s dedotti.' . "\n\n",
	'PM_MESSAGE_NOMINATED'			=> '%1$s' . "\n" . '[b]Ti è stata assegnata la medaglia "%2$s" da %3$s dopo esser stato nominato da %4$s.' . "\n" . '%3$s inviato anche il seguente messaggio:[/b]' . "\n\n",
	'PM_MSG_SUBJECT'				=> '%s si è aggiudicato una medaglia!',

// medals awarding
	'AWARDED_BY'					=> 'Premiato da',
	'AWARDED_MEDAL'					=> 'Medaglie assegnate',
	'AWARDED_MEDAL_TO'				=> 'medaglie assegnate di',
	'AWARD_MEDAL'					=> 'Medaglia assegnata',
	'AWARD_TIME'					=> 'Assegnata in data',
	'AWARD_TO'						=> 'Assegnata da',
	'MEDAL_AWARD_GOOD'				=> 'Medaglia assegnata con successo!',
	'NOT_MEDALS_AWARDED'			=> 'Medaglia non assegnata !',
	'MEDAL_REMOVE_GOOD'				=> 'Medaglia rimossa con successo !',
	'MEDAL_REMOVE_CONFIRM'			=> 'Stai per rimuovere una medaglia ad un utente/i ! Sei sicuro di voler effettuare questa operazione ?',
	'MEDAL_REMOVE_NO'				=> 'Nessuna medaglia rimossa ',
	'MEDAL_EDIT'					=> 'Modifica',
	'NO_USER_SELECTED'				=> 'Nessun nome inserito. Verrai momentaneamente redirectato',

// medals nominate
	'APPROVE'						=> 'Approva',
	'USER_NOMINATED'				=> 'Utente nomina',
	'USER_IS_NOMINATED'				=> ' [<a href="%s" title="Questo utente è stato nominato per una medaglia!">!</a>]',
	'MEDAL_NOMINATE_GOOD'			=> 'Medaglia nominato con successo!',
	'NOMINATABLE'					=> '[Nominabile]',
	'NOMINATE'						=> 'Medaglia nominata',
	'NOMINATE_FOR'					=> 'Medaglia nominata per',
	'NOMINATE_MEDAL'				=> 'Opzioni nomina',
	'NOMINATE_MESSAGE'				=> '<strong>%1$s nomina utente per la medaglia "%2$s" per il seguente motivo:</strong>' . "\n\n",
	'NOMINATE_USER_LOG'				=> 'Gestire candidature per %s',
	'NOMINATED_BY'					=> '[Nominato da %s]',
	'NOMINATED_EXPLAIN'				=> 'Gli utenti possono nominare altri utenti per questa medaglia ?',
	'NOMINATED_TITLE'				=> 'Nominazione Medaglia',
	'NO_MEDALS_NOMINATED'			=> 'Medaglia non nominata',
	'NOMINATIONS_REMOVE_GOOD'		=> 'Nominazione rimossa con successo !',

// Images
	'IMAGE_PREVIEW'					=> 'Anteprima',
	'MEDAL_IMG'						=> 'Immagine',

// medals view
	'MEDAL'							=> 'Medaglia',
	'MEDALS'						=> 'Medaglie',
	'MEDALS_VIEW_BUTTON'			=> 'Premio Dettagli',
	'MEDALS_VIEW'					=> 'Medaglie',
	'MEDAL_DETAIL'					=> 'Dettagli Medaglie',
	'MEDAL_DESCRIPTION'				=> 'Medaglia Descrizione',
	'MEDAL_DESC'					=> 'Descrizione',
	'MEDAL_AWARDED'					=> 'Recipiente',
	'MEDAL_AWARDED_EXPLAIN'			=> '<br />Click su username per amministrare le sue medaglie',
	'MEDAL_AWARD_REASON'			=> 'AMotivo premio',
	'MEDAL_AWARD_REASON_EXPLAIN'	=> '<br />Inserisci il motivo per assegnare questa medaglia',
	'MEDAL_NOMINATE_REASON'			=> 'Motivo nomina',
	'MEDAL_NOMINATE_REASON_EXPLAIN'	=> '<br />Immettere la ragione per la nomina questa medaglia',
	'MEDAL_AWARD_USER_EXPLAIN'		=> '<br />Inserire gli utenti da attribuire questa medaglia (ogni nome su una riga separata)',
	'MEDAL_INFORMATION'				=> 'Medaglia Informazione',
	'MEDAL_INFO'					=> 'Informazione',
	'MEDAL_MOD'						=> 'Premio',
	'MEDAL_NAME'					=> 'Nome',
	'NO_MEDALS_ISSUED'				=> 'Medaglia Non Emessa',
	'MEDAL_CP'						=> 'Pannello di controllo medaglie',
	'MEDAL_AWARD_PANEL'				=> 'Pannello Medaglie Nominate',
	'MEDAL_NOM_BY'					=> 'Nominate da',
	'MEDAL_AMOUNT'					=> 'Importo',

// Error messages
	'CANNOT_AWARD_MULTIPLE'	=> 'A questo utente è stato assegnato un importo massimo assegnato a questa medaglia',
	'IMAGE_ERROR'			=> 'Non è possibile selezionare questo come una medaglia premio ',
	'IMAGE_ERROR_NOM'		=> 'Non è possibile selezionare questo come una medaglia da nominare',
	'NO_CAT_ID'				=> 'Non è stato specificato nessun ID di categoria specifico.',
	'NO_CATS'				=> 'Nessuna categoria',
	'NO_GOOD_PERMS'			=> 'Non avete i permessi necessari per accedere a questa pagina.<br /><br /><a href="index.php">Ritorna in Index</a>',
	'NO_MEDAL_ID'			=> 'Nessuna medaglia è stata selezionata o non sono disponibili. Sarai reindirizzato momentaneamente',
	'NO_MEDAL_MSG'			=> 'Il campo del messaggio è vuoto.<br /><br /><a href="%s">Ritorna alla precedente pagina</a>',
	'NO_MEDALS'				=> 'Nessuna Medaglia disponibile',
	'NO_MEDALS_TO_NOMINATE'	=> 'Non ci sono medaglie a disposizione di nominare a questo utente<br /><br /><a href="%s">Ritorna alla precedente pagina</a>',
	'NO_USER_ID'			=> 'Utente o ID non specificato',
	'NO_USER_MEDALS'		=> 'A questo utente non è stato assegnato nessuna medaglia',
	'NO_USER_NOMINATIONS'	=> 'A questo utente non è stato nominata nessuna medaglia',
	'NO_SWAP_ID'			=> 'Nessun ID specificato',
	'NOT_SELF'				=> 'Non si puoi candidare te stesso',

	'EXT_AUTHOR_COPY'		=> 'BB3.Mobi &copy; <a href="http://bb3.mobi/forum/viewtopic.php?t=78">Medals System Extension</a>. Traduzione Italiana di <a href="http://www.microcosmoacquari.it/forum/">Microcosmo</a>.',
));
