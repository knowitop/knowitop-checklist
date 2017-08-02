<?php

//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'knowitop-checklist/1.0.0',
	array(
		// Identification
		//
		'label' => "Checklist module by _knowitop",
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			'itop-tickets/2.3.0',
            'knowitop-template-base/1.0.0'
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'htmlrenderer.class.php',
			'main.knowitop-checklist.php',
			'model.knowitop-checklist.php'
		),
		'webservice' => array(

		),
		'data.struct' => array(
			// add your 'structure' definition XML files here,
		),
		'data.sample' => array(
			// add your sample data XML files here,
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
        // TODO: add libs licenses info
		'doc.more_information' => '', // hyperlink to more information, if any

		// Default settings
		//
		'settings' => array(
			// Module specific settings go here, if any
            'allowed_classes' => array(
                0 => 'WorkOrder',
                1 => 'Ticket'
            ),
            'edit_in_place' => true
		),
	)
);
