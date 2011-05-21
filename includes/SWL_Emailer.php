<?php

/**
 * Static class holding functions for sending emails.
 * 
 * @since 0.1
 * 
 * @file SWL_Emailer.php
 * @ingroup SemanticWatchlist
 * 
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class SWLEmailer {
	
    /**
     * Notifies a single user of the changes made to properties in a single edit.
     * 
     * @since 0.1
     * 
     * @param SWLGroup $group
     * @param User $user
     * @param SWLChangeSet $changeSet
     * @param boolean $describeChanges
     * 
     * @return Status
     */
    public static function notifyUser( SWLGroup $group, User $user, SWLChangeSet $changeSet, $describeChanges ) {
    	global $wgLang;
    	
    	$emailText = wfMsgExt(
    		'swl-email-propschanged-long',
    		'parse', 
    		$GLOBALS['wgSitename'],
    		$changeSet->getUser()->getName(),
    		SpecialPage::getTitleFor( 'SemanticWatchlist' )->getFullURL(),
    		$wgLang->time( $changeSet->getTime() ),
    		$wgLang->date( $changeSet->getTime() )
    	);
    	
    	if ( $describeChanges ) {
	    	$emailText .= '<h3> ' . wfMsgExt(
	    		'swl-email-changes',
	    		'parse', 
	    		$changeSet->getTitle()->getFullText(),
	    		$changeSet->getTitle()->getFullURL()
	    	) . ' </h3>';
	    	
	    	$emailText .= self::getChangeListHTML( $changeSet );    		
    	}
    	
    	//echo $emailText;exit;
    	
    	return $user->sendMail(
    		wfMsgReal( 'swl-email-propschanged', array(), true, $user->getOption( 'language' ) ),
    		$emailText
    	);
    }
    
    /**
     * Creates and returns the HTML representatation of the change set.
     * 
     * @since 0.1
     * 
     * @param SWLChangeSet $changeSet
     * 
     * @return string
     */
    protected static function getChangeListHTML( SWLChangeSet $changeSet ) {
    	$propertyHTML = array();
    	
    	foreach ( $changeSet->getAllProperties() as /* SMWDIProperty */ $property ) {
    		$propertyHTML[] = self::getPropertyHTML( $property, $changeSet->getAllPropertyChanges( $property ) );
    	}
    	
    	return implode( '', $propertyHTML );
    }
    
    /**
     * Creates and returns the HTML representatation of the property and it's changes.
     * 
     * @since 0.1
     * 
     * @param SMWDIProperty $property
     * @param array $changes
     * 
     * @return string
     */
    protected static function getPropertyHTML( SMWDIProperty $property, array $changes ) {
		$insertions = array();
		$deletions = array();
		
		// Convert the changes into a list of insertions and a list of deletions.
		foreach ( $changes as /* SWLPropertyChange */ $change ) {
			if ( !is_null( $change->getOldValue() ) ) {
				$deletions[] = SMWDataValueFactory::newDataItemValue( $change->getOldValue(), $property )->getShortHTMLText();
			}
			if ( !is_null( $change->getNewValue() ) ) {
				$insertions[] = SMWDataValueFactory::newDataItemValue( $change->getNewValue(), $property )->getShortHTMLText();
			}
		}
		
		$lines = array();
		
		if ( count( $insertions ) > 0 ) {
			$lines[] = Html::element( 'span', array(), wfMsg( 'swl-watchlist-insertions' ) ) . ' ' . implode( ', ', $insertions );
		}
		
		if ( count( $deletions ) > 0 ) {
			$lines[] = Html::element( 'span', array(), wfMsg( 'swl-watchlist-deletions' ) ) . ' ' . implode( ', ', $deletions );
		}		
		
		$html = Html::element( 'b', array(), $property->getLabel() );
		
		$html .= Html::rawElement(
			'div',
			array( 'class' => 'swl-prop-div' ),
			implode( '<br />', $lines )
		);
		
		return $html;    	
    }
	
}
