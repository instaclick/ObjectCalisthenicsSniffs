<?php
/**
 * Parse tree visitor for control structures
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class ObjectCalisthenics_Sniffs_ControlStructures_OneIndentationLevelSniff_NodeVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var \PHP_CodeSniffer_File
     */
    private $phpcsFile;

    /**
     * @var array
     */
    private $functionScopeStack;

    /**
     * Set PHP CodeSniffer File
     *
     * @param \PHP_CodeSniffer_File $phpcsFile
     */
    public function setPHPCodeSnifferFile(PHP_CodeSniffer_File $phpcsFile)
    {
        $this->phpcsFile = $phpcsFile;
    }

    /**
     * Search token stream for the corresponding node
     *
     * @param \PHPParser_Node $node Current node
     *
     * @return integer
     */
    private function findStackPointer($node)
    {
        $tokens = $this->phpcsFile
                       ->getTokens();

        foreach ($tokens as $stackPtr => $token) {
            if ($node->getLine() > $token['line']) {
                continue;
            }

            return $stackPtr;
        }
    }

    /**
     * Report coding standard violation if nested control structure detected
     *
     * @param \ObjectCalisthenics_Sniffs_ControlStructures_TokenStack $tokenStack Token stack
     * @param \PHPParser_Node                                         $node       Current node
     */
    private function checkNesting($tokenStack, $node)
    {
        if ( ! $tokenStack->isNested()) {
            return;
        }

        $this->phpcsFile
             ->addError('Only one level of indentation per method/function', $this->findStackPointer($node));
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTraverse(array $nodes)
    {
        $this->functionScopeStack = array(new ObjectCalisthenics_Sniffs_ControlStructures_OneIndentationLevelSniff_TokenStack);
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(PHPParser_Node $node)
    {
        switch ($node->getType()) {
            case 'Stmt_ClassMethod':
            case 'Expr_Closure':
            case 'Stmt_Function':
                array_push($this->functionScopeStack, new ObjectCalisthenics_Sniffs_ControlStructures_OneIndentationLevelSniff_TokenStack);
                break;

            case 'Stmt_If':
            case 'Stmt_Do':
            case 'Stmt_While':
            case 'Stmt_For':
            case 'Stmt_Foreach':
                $tokenStack = end($this->functionScopeStack);
                $tokenStack->push($node->getType());

                $this->checkNesting($tokenStack, $node);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(PHPParser_Node $node)
    {
        switch ($node->getType()) {
            case 'Stmt_ClassMethod':
            case 'Expr_Closure':
            case 'Stmt_Function':
                array_pop($this->functionScopeStack);
                break;

            case 'Stmt_If':
            case 'Stmt_Do':
            case 'Stmt_While':
            case 'Stmt_For':
            case 'Stmt_Foreach':
                $tokenStack = end($this->functionScopeStack);
                $tokenStack->pop();
                break;
        }
    }
}
