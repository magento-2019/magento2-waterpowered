<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Acm\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'mgs_acm_types'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_types')
        )->addColumn(
            'acm_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Acm Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'identifier',
            Table::TYPE_TEXT,
            255,
            [],
            'Identifier'
        )->addColumn(
            'content_type',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Content Type'
        )->addColumn(
            'form_captcha',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Enable CAPTCHA'
        )->addColumn(
            'form_legend',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Legend'
        )->addColumn(
            'form_note',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Note for form'
        )->addColumn(
            'success_message',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Success Message'
        )->addColumn(
            'form_action',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Action of Form'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Email'
        )->addColumn(
            'reply_email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Email to reply'
        )->addColumn(
            'template_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Template Email Id'
        )->addColumn(
            'layout',
            Table::TYPE_TEXT,
            255,
            [],
            'Layout'
        )->addColumn(
            'layout_update_xml',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Layout Update Xml'
        )->addColumn(
            'template',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Template File Path'
        )->addColumn(
            'template_products',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Template File Path for products'
        )->addColumn(
            'template_products_detail',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Template File Path for products on details page'
        )->addColumn(
            'template_detail',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Template File Path for detail page'
        )->addColumn(
            'page_size',
            Table::TYPE_INTEGER,
            null,
            [],
            'Limit on list page'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Description'
        )->addColumn(
            'meta_description',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Description'
        )->addColumn(
            'meta_keyword',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Keyword'
        )->addColumn(
            'meta_robots',
            Table::TYPE_TEXT,
            255,
            [],
            'Meta Robots'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Is Active'
        )->addColumn(
            'breadcrumbs',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Show Breadcrumbs or not'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Update Time'
        );

        $installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_acm_field'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_field')
        )->addColumn(
            'field_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Field Id'
        )->addColumn(
            'acm_type_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Type Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'identifier',
            Table::TYPE_TEXT,
            255,
            [],
            'Identifier'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            [],
            'Type'
        )->addColumn(
            'additional_content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Additional content'
        )->addColumn(
            'is_required',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 0],
            'Is Required'
        )->addColumn(
            'in_grid',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 0],
            'Show In Grid'
        )->addColumn(
            'note',
            Table::TYPE_TEXT,
            255,
            [],
            'Note'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => 0],
            'Position'
        );

        $installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_acm_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_item')
        )->addColumn(
            'item_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Item Id'
        )->addColumn(
            'acm_type_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Type Id'
        )->addColumn(
            'url_key',
            Table::TYPE_TEXT,
            255,
            [],
            'Url Key'
        )->addColumn(
            'layout',
            Table::TYPE_TEXT,
            255,
            [],
            'Layout'
        )->addColumn(
            'layout_update_xml',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Layout Update Xml'
        )->addColumn(
            'page_title',
            Table::TYPE_TEXT,
            255,
            [],
            'Page Title'
        )->addColumn(
            'meta_description',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Description'
        )->addColumn(
            'meta_keyword',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Keyword'
        )->addColumn(
            'meta_robots',
            Table::TYPE_TEXT,
            255,
            [],
            'Meta Robots'
        );

        $installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_acm_item_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_item_store')
        )->addColumn(
            'item_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true],
            'Item ID'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        );
		$installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_acm_value'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_value')
        )->addColumn(
            'value_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Value ID'
        )->addColumn(
            'acm_field_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Field ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Value'
        );
		$installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_acm_item_value'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_item_value')
        )->addColumn(
            'item_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Item ID'
        )->addColumn(
            'value_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Value ID'
        );
		$installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_acm_item_products'
         */
		$table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_acm_item_products')
		)->addColumn(
			'entity_id',
			Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'item_id',
			Table::TYPE_INTEGER,
			null,
			['unsigned' => true, 'nullable' => false],
			'Item Id'
		)->addColumn(
			'product_id',
			Table::TYPE_INTEGER,
			null,
			['unsigned' => true, 'nullable' => false],
			'Product Id'
		)->addColumn(
			'position',
			Table::TYPE_INTEGER,
			null,
			['unsigned' => true, 'nullable' => false],
			'Position'
		);
		
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
