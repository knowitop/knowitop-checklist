<?php

//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'knowitop-checklist/1.3.0',
	array(
		// Identification
		//
		'label' => "Checklist module",
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			'itop-tickets/3.0.0',
			'knowitop-object-template-base/1.0.0',
			'knowitop-object-template-workorder/1.0.0'
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'vendor/autoload.php',
			'model.knowitop-checklist.php',
			'src/Hook/ChecklistPlugin.php'
		),
		'webservice' => array(),
		'data.struct' => array(// add your 'structure' definition XML files here,
		),
		'data.sample' => array(// add your sample data XML files here,
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
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
