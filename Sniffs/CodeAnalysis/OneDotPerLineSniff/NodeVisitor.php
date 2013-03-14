<?php
/**
 * Parse tree visitor for control structures
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class ObjectCalisthenics_Sniffs_CodeAnalysis_OneDotPerLineSniff_NodeVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var \PHP_CodeSniffer_File
     */
    private $phpcsFile;

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
     * {@inheritdoc}
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node->getAttribute('visited')) {
            return;
        }
return;
        switch ($node->getType()) {
            case 'Stmt_ClassMethod':
            case 'Expr_Closure':
            case 'Stmt_Function':
            case 'Stmt_If':
            case 'Stmt_Do':
            case 'Stmt_While':
            case 'Stmt_For':
            case 'Stmt_Foreach':
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(PHPParser_Node $node)
    {
return;
        switch ($node->getType()) {
            case 'Stmt_ClassMethod':
            case 'Expr_Closure':
            case 'Stmt_Function':
            case 'Stmt_If':
            case 'Stmt_Do':
            case 'Stmt_While':
            case 'Stmt_For':
            case 'Stmt_Foreach':
                break;
        }
    }
}
