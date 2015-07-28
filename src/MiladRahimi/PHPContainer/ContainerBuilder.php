<?php namespace MiladRahimi\PHPContainer;

/**
 * Class ContainerBuilder
 * ContainerBuilder creates new Container instances
 *
 * @package MiladRahimi\PHPContainer
 * @author Milad Rahimi <info@miladrahimi.com>
 */
class ContainerBuilder
{
    /**
     * Create new Container instance
     *
     * @return Container
     */
    public static function build() {
        return new Container();
    }
}