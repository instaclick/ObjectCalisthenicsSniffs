<?php
/**
 * Parse tree visitor for class properties
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class ObjectCalisthenics_Sniffs_Functions_UseAccessorsSniff_NodeVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var \PHPParser_node
     */
    private $classNode;

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
     * Report coding standard violation if not a getter
     *
     * @param \PHPParser_Node $node Current node
     */
    private function checkGetter($node)
    {
        if (strncmp($node->name, 'get', 3) ||
            $node->name === 'get' ||
            $node->type !== 1 ||
            isset($node->params) &&
            count($node->params) === 0 &&
            isset($node->stmts[0]) &&
            count($node->stmts) === 1 &&
            $node->stmts[0]->getType() === 'Stmt_Return'
        ) {
           return;
        }

        $this->phpcsFile
             ->addWarning('Suspicious looking getter', $this->findStackPointer($node));
    }

    /**
     * Report coding standard violation if not a setter
     *
     * @param \PHPParser_Node $node Current node
     */
    private function checkSetter($node)
    {
        if (strncmp($node->name, 'set', 3) ||
            $node->type !== 1 ||
            isset($node->params) &&
            count($node->params) === 1 &&
            isset($node->stmts[0]) &&
            count($node->stmts) === 1 &&
            ($node->stmts[0]->getType() === 'Expr_Assign' ||
            $node->stmts[0]->getType() == 'Expr_MethodCall')
        ) {
           return;
        }

        $this->phpcsFile
             ->addWarning('Suspicious looking setter', $this->findStackPointer($node));
    }

    /**
     * Report coding standard violation if adder has List
     *
     * @param \PHPParser_Node $node Current node
     */
    private function checkAdder($node)
    {
        if ( ! preg_match('/^(add.*)List$/', $node->name, $matches)) {
            return;
        }

        $this->phpcsFile
             ->addError(sprintf('Invalid accessor name for adder. Did you mean %s?', $matches[1]), $this->findStackPointer($node));
    }

    /**
     * Report coding standard violation if adder has And
     *
     * @param \PHPParser_Node $node Current node
     */
    private function checkAnder($node)
    {
        if ( ! preg_match('/[A-Za-z]And[A-Z]$/', $node->name)) {
            return;
        }

        $this->phpcsFile
             ->addWarning('Method may be doing too much (AND)', $this->findStackPointer($node));
    }

    /**
     * Report coding standard violation if public properties
     *
     * @param \PHPParser_Node $node Current node
     */
    private function checkPublicProperty($node)
    {
        if ($node->type !== 1) {
            return;
        }

        $this->phpcsFile
             ->addWarning('Use getter/setter instead of public property', $this->findStackPointer($node));
    }

    /**
     * Report coding standard violation if protected properties
     *
     * @param \PHPParser_Node $node Current node
     */
    private function checkProtectedProperty($node)
    {
        if ($this->classNode->type === 16 || $node->type !== 2) {
            return;
        }

        if ($this->classNode->type === 32) {
            $this->phpcsFile
                 ->addError('Protected properties in final class', $this->findStackPointer($node));

            return;
        }

        $this->phpcsFile
             ->addWarning('Concrete class should not have protected properties', $this->findStackPointer($node));
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(PHPParser_Node $node)
    {
        switch ($node->getType()) {
            case 'Stmt_Class':
                $this->classNode = $node;
                break;

            case 'Stmt_Property':
                $this->checkPublicProperty($node);
                $this->checkProtectedProperty($node);
                break;

            case 'Stmt_ClassMethod':
                $this->checkGetter($node);
                $this->checkSetter($node);
                $this->checkAdder($node);
                $this->checkAnder($node);
                break;
        }
    }
}
