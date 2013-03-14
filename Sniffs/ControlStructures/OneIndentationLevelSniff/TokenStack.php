<?php
/**
 * Token stack
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class ObjectCalisthenics_Sniffs_ControlStructures_OneIndentationLevelSniff_TokenStack
{
    /**
     * @var array
     */
    private $tokenStack = array();

    /**
     * Push token
     *
     * @param \PHP_CodeSniffer_Node $node
     */
    public function push($node)
    {
        array_push($this->tokenStack, $node);
    }

    /**
     * Pop token
     */
    public function pop()
    {
        array_pop($this->tokenStack);
    }

    /**
     * Are there two or more control structures on the stack?
     *
     * @return boolean
     */
    public function isNested()
    {
        $count = count($this->tokenStack);

        if ($count < 2 ||
            $count === 2 && in_array($this->tokenStack[0], array('Stmt_Do', 'Stmt_While', 'Stmt_For', 'Stmt_Foreach')) && $this->tokenStack[1] === 'Stmt_If'
        ) {
            return false;
        }

        return true;
    }
}
