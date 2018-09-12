<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace MGS\Acm\Model\ResourceModel;

/**
 * Admin rule resource model
 */
class Rules extends \Magento\Authorization\Model\ResourceModel\Rules
{
    /**
     * Save ACL resources
     *
     * @param \Magento\Authorization\Model\Rules $rule
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveRel(\Magento\Authorization\Model\Rules $rule)
    {
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $roleId = $rule->getRoleId();

            $condition = ['role_id = ?' => (int)$roleId];

            $connection->delete($this->getMainTable(), $condition);

            $postedResources = $rule->getResources();
            if ($postedResources) {
                $row = [
                    'resource_id' => $this->_rootResource->getId(),
                    'privileges' => '', // not used yet
                    'role_id' => $roleId,
                    'permission' => 'allow',
                ];

                // If all was selected save it only and nothing else.
                if ($postedResources === [$this->_rootResource->getId()]) {
                    $insertData = $this->_prepareDataForTable(new \Magento\Framework\DataObject($row), $this->getMainTable());

                    $connection->insert($this->getMainTable(), $insertData);
                } else {
                    $acl = $this->_aclBuilder->getAcl();
                    /** @var $resource \Magento\Framework\Acl\AclResource */
					$allResource = $acl->getResources();

					$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Acm\Helper\Data');
					$contentTypes = $helper->getContentTypes();
					$_collectionSize = count($contentTypes);
					if($_collectionSize>0){
						$allResource[] = 'Magento_Backend::acm';
						foreach($contentTypes as $type){
							$allResource[] = 'MGS_Acm::'.$type->getIdentifier();
						}
					}
					
                    foreach ($allResource as $resourceId) {
                        $row['permission'] = in_array($resourceId, $postedResources) ? 'allow' : 'deny';
                        $row['resource_id'] = $resourceId;

                        $insertData = $this->_prepareDataForTable(new \Magento\Framework\DataObject($row), $this->getMainTable());
                        $connection->insert($this->getMainTable(), $insertData);
                    }
                }
            }

            $connection->commit();
            $this->_aclCache->clean();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }
}
