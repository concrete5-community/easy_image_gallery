<?php

namespace Concrete\Package\EasyImageGallery\Attribute\PageSelector;

use Concrete\Core\Attribute\Controller as CoreController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends CoreController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::$searchIndexFieldDefinition
     */
    protected $searchIndexFieldDefinition = [
        'type' => 'integer',
        'options' => ['default' => 0, 'notnull' => false],
    ];

    /**
     * @return int
     */
    public function getValue()
    {
        $cn = $this->app->make(Connection::class);

        return (int) $cn->fetchColumn('select value from atPageSelector where avID = ?', [$this->getAttributeValueID()]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getDisplayValue()
     *
     * @return string|null
     */
    public function getDisplayValue()
    {
        $cID = $this->getValue();
        if ($cID > 0) {
            $c = Page::getByID($cID);
            if ($c && !$c->isError()) {
                return '<a href="' . h($c->getCollectionLink()) . '">' . h($c->getCollectionName()) . '</a>';
            }
        }
    }

    /**
     * @return string|null
     */
    public function getDisplaySanitizedValue()
    {
        return $this->getDisplayValue();
    }

    public function searchForm($list)
    {
        $cID = $this->request('value');
        $list->filterByAttribute($this->attributeKey->getAttributeKeyHandle(), $cID, '=');

        return $list;
    }

    public function search()
    {
        $formSelector = $this->app->make('helper/form/page_selector');
        echo $formSelector->selectPage($this->field('value'), $this->request('value'), false);
    }

    public function form()
    {
        $value = is_object($this->attributeValue) ? $this->getAttributeValue()->getValue() : null;
        $formSelector = $this->app->make('helper/form/page_selector');
        echo $formSelector->selectPage($this->field('value'), $value);
    }

    /**
     * @param array $p
     *
     * @return bool
     */
    public function validateForm($p)
    {
        if (empty($p['value'])) {
            return false;
        }
        return ((int) $p['value']) !== 0;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::saveValue()
     */
    public function saveValue($value)
    {
        $cn = $this->app->make(Connection::class);
        $value = (int) $value;
        $cn->replace(
            // $table
            'atPageSelector',
            // $fieldArray
            ['avID' => $this->getAttributeValueID(), 'value' => $value],
            // $keyCol
            'avID',
            // $autoQuote
            true
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::deleteKey()
     */
    public function deleteKey()
    {
        $cn = $this->app->make(Connection::class);
        $arr = $this->attributeKey->getAttributeValueIDList();
        foreach($arr as $id) {
            $cn->delete('atPageSelector', ['avID' => $id]);
        }
        parent::deleteKey();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::saveForm()
     */
    public function saveForm($data)
    {
        $this->saveValue(isset($data['value']) ? $data['value'] : null);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::deleteValue()
     */
    public function deleteValue()
    {
        $cn = $this->app->make(Connection::class);
        $cn->delete('atPageSelector', ['avID' => $this->getAttributeValueID()]);
        parent::deleteValue();
    }
}
