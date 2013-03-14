<?php
/**
 * Parse tree visitor for classes and methods
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class ObjectCalisthenics_Sniffs_Classes_SmallClassSniff_NodeVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $metaData = array(
        'Stmt_Class' => array(
            'keyword' => 'class',
            'maximum' => 200,
        ),
        'Stmt_Interface' => array(
            'keyword' => 'interface',
            'maximum' => 200,
        ),
        'Stmt_Trait' => array(
            'keyword' => 'trait',
            'maximum' => 200,
        ),
        'Stmt_ClassMethod' => array(
            'keyword' => 'method',
            'maximum' => 25,
        ),
    );

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
     * Report coding standard violation if class/interface/trait/function is too big
     *
     * @param \PHPParser_Node $node Current node
     *
     * {@internal 'maximum' is padded to compensate for opening/closing curly braces }}
     */
    private function checkSize($node)
    {
        $keyword = $this->metaData[$node->getType()]['keyword'];
        $maximum = $this->metaData[$node->getType()]['maximum'];
        $size    = $node->getAttribute('endLine') - $node->getAttribute('startLine');

        if ($size <= $maximum + 2) {
            return;
        }

        $this->phpcsFile
             ->addWarning(sprintf('Keep your %s small (no more than %d lines)', $keyword, $maximum), $this->findStackPointer($node), ucfirst($keyword).'TooBig');
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(PHPParser_Node $node)
    {
        if ( ! isset($this->metaData[$node->getType()])) {
            return;
        }

        $this->checkSize($node);
    }
}
