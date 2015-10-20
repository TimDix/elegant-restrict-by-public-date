<?php 
namespace Concrete\Package\ElegantRestrictByPublicDate;

defined('C5_EXECUTE') or die('Access Denied.');

use Events;
use Package;
use Group;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\Permission\Access\Access as PermissionAccess;
use Concrete\Core\Permission\Key\PageKey as PagePermissionKey;
use \Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
use \Concrete\Core\Permission\Duration as PermissionDuration;


class Controller extends Package
{

    protected $pkgHandle = 'elegant_restrict_by_public_date';
    protected $appVersionRequired = '5.7.1';
    protected $pkgVersion = '1.0.0';

    public function getPackageDescription()
    {
        return t("Uses permissions to restrict access until the public date.");
    }

    public function getPackageName()
    {
        return t('Elegant Restrict By Public Date');
    }

    public function on_start()
    {
        Events::addListener('on_page_update', array($this, 'handlePageUpdate'));
    }

    public function handlePageUpdate($event)
    {

        $page = $event->getPageObject();

        $pk = PermissionKey::getByHandle('view_page');
        $pk->setPermissionObject($page);
        $list = $pk->getAccessListItems();

        foreach($list as $pa) {
            $pae = $pa->getAccessEntityObject();
            if ($pae->getAccessEntityTypeHandle() == 'group') {
                if ($pae->getGroupObject()->getGroupID() == GUEST_GROUP_ID) {
                    $pd = $pa->getPermissionDurationObject();
                    if (!is_object($pd)) {
                        $pd = new PermissionDuration();
                    }

                    $publicDate = strtotime($page->getCollectionDatePublic());

                    $pd->setStartDateAllDay(0);
                    $pd->setEndDateAllDay(0);
                    $pd->setStartDate( $dateStart = date('Y-m-d H:i:s', $publicDate));

                    $pd->save();

                    $paa = PermissionAccess::getByID($pa->paID, $pk);
                    $paa->addListItem($pae, $pd, PermissionKey::ACCESS_TYPE_INCLUDE);
                }
            }
        }
    }

    public function install()
    {
        $pkg = parent::install();

    }

}
