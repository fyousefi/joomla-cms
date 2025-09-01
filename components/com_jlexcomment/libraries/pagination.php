<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JCmPagination {

    protected $page     = null;

    protected $offset   = 0;

    protected $limit    = 20;

    protected $total    = 0;

    protected $pageNumber     = 1;

    protected $parent_id = 0;

    public function __construct ($page = 1, $limit = 2)
    {
        $this->limit = $limit;
        $this->pageNumber = $page;
        $this->offset = ( $this->pageNumber - 1 ) * $this->limit;
    }

    public function get ($varname, $default = null)
    {
        if ( property_exists($this, $varname) )
        {
            return $this->$varname;
        }

        return $default;
    }

    public function set ($varname, $value)
    {
        if ( property_exists($this, $varname) )
        {
            $this->$varname = $value;
        }
    }

    public function init ()
    {
        if ($this->limit >= $this->total)
        {
            return $this;
        }

        $totalPage = intval($this->total / $this->limit);
        $totalPage += $this->total % $this->limit != 0 ? 1 : 0;
        $pageCurrent = 1;
        $this->offset += 1;

        if ($this->offset > $this->limit)
        {
            $pageCurrent = intval($this->offset / $this->limit);
            $pageCurrent += $this->offset % $this->limit != 0 ? 1 : 0;
        }
        
        $this->page             = new stdClass();
        $this->page->prev       = $pageCurrent != 1;
        $this->page->next       = $pageCurrent != $totalPage;
        $this->page->pages      = array ();
        $this->page->ellipsis   = false;
        $this->page->active     = $pageCurrent;
        $this->page->total      = $totalPage;

        if ($totalPage > 6) {
            $this->page->ellipsis = true;
            if ($pageCurrent - 1 <= 1) {
                $this->page->pages [] = 2;
                $this->page->pages [] = 3;
                $this->page->pages [] = 4;
            } elseif ($totalPage - $pageCurrent <= 1) {
                $this->page->pages [] = $totalPage - 1;
                $this->page->pages [] = $totalPage - 2;
                $this->page->pages [] = $totalPage - 3;
            } else {
                $this->page->pages [] = $pageCurrent;
                $this->page->pages [] = $pageCurrent - 1;
                $this->page->pages [] = $pageCurrent + 1;
            }
            
            // Sort again page number
            $this->page->pages  = array_unique( $this->page->pages );
            sort ($this->page->pages, SORT_NUMERIC);
        } else {
            if ($totalPage > 2) {
                for ($i = 2; $i <= $totalPage - 1; $i ++) {
                    $this->page->pages [] = $i;
                }
            }
        }

        return $this;
    }

    public function toHtml ($url='')
    {
        if ( $this->page == null )
        {
            return '';
        }

        if (!preg_match("/^\s*$/", $url))
        {
            $url.= (preg_match("/\?/", $url) ? "&" : "?") . "page_comment=" ;
        } else {
            $url = "";
        }

        ob_start();
        ?>
        <ul class="jcm-root-page unstyled jcm-inline">
            <?php if ($this->page->ellipsis): ?>
            <li class="first <?php echo !$this->page->prev?'a-disabled':'' ?>">
                <?php if( $this->page->prev ): ?>
                    <a href="<?php echo $url!=""?$url.($this->page->active-1) : JRoute::_('&page_comment='.($this->page->active-1)); ?>" data-page="<?php echo $this->page->active-1; ?>">
                        <span>&#8592;&nbsp;</span>
                        <span><?php echo JText::_("JCM_PREVIOUS"); ?></span>
                    </a>
                <?php else: ?>
                    <span>&#8592;&nbsp;</span>
                    <span><?php echo JText::_("JCM_PREVIOUS"); ?></span>
                <?php endif; ?>
            </li>
            <?php endif; ?>
            
            <li class="page-button <?php echo 1==$this->page->active?'a-selected':'' ?>">
                <a href="<?php echo $url!=""?$url."1" : JRoute::_('&page_comment=1'); ?>" data-page="1">1</a>
            </li>
            
            <?php if( $this->page->ellipsis && $this->page->active>3): ?>
            <li class="a-disabled page-ellipsis">...</li>
            <?php endif; ?>
            
            <?php foreach ($this->page->pages as $p): ?>
            <li class="page-button <?php echo $p==$this->page->active?'a-selected':'' ?>">
                <a href="<?php echo $url!=""?$url.$p : JRoute::_('&page_comment='.$p); ?>" data-page="<?php echo $p ?>"><?php echo $p ?></a>
            </li>
            <?php endforeach; ?>
            
            <?php if( $this->page->ellipsis && $this->page->active < $this->page->total-2): ?>
            <li class="a-disabled page-ellipsis">...</li>
            <?php endif; ?>
            
            <?php if( $this->page->total>1):?>
            <li class="page-button <?php echo $this->page->total==$this->page->active?'a-selected':'' ?>">
                <a href="<?php echo $url!=""?$url.$this->page->total : JRoute::_('&page_comment='.$this->page->total); ?>" data-page="<?php echo $this->page->total ?>"><?php echo $this->page->total; ?></a>
            </li>
            <?php endif; ?>
            
            <?php if($this->page->ellipsis): ?>
            <li class="last <?php echo !$this->page->next?'a-disabled':'' ?>">
                <?php if($this->page->next): ?>
                    <a href="<?php echo $url!=""?$url.($this->page->active+1) : JRoute::_('&page_comment='.($this->page->active+1)); ?>" data-page="<?php echo $this->page->active+1 ?>">
                        <span><?php echo JText::_("JCM_NEXT"); ?></span>
                        <span>&nbsp;&#8594;</span>
                    </a>
                <?php else: ?>
                    <span><?php echo JText::_("JCM_NEXT"); ?></span>
                    <span>&nbsp;&#8594;</span>
                <?php endif; ?>
            </li>
            <?php endif; ?>
        </ul>
        <?php

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function child ()
    {
        $totalPage = intval($this->total / $this->limit);
        $totalPage += $this->total % $this->limit != 0 ? 1 : 0;

        $data = '<input type="hidden" class="jcm-pagination-data" ';
            $data .= 'data-pid="'.$this->parent_id.'" ';
            $data .= 'data-total="'.$totalPage.'" ';
            $data .= 'data-page="'.$this->pageNumber.'" ';
            $data .= 'data-limit="'.$this->limit.'" />';
        return $data;
    }
}