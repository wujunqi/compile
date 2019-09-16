<?php
    /**
     * 一个简单的手写词法分析器
     * 能够为后面的简单计算器、简单脚本语言产生Token
     */
    class SimpleLexer {
        //解析过程中用到的临时变量
        private $tokenText = ''; //临时保存token的文本
        private $tokens = array();//保存解析出来的Token
        private $token = array();//当前正在解析的Token(type, text)

        /**
         * 有限状态机的各种状态
         */
        const Dfastage_Initial = 'Initial';
        const Dfastage_If = 'If';
        const Dfastage_Id_if1 = 'Id_if1';
        const Dfastage_Id_if2 = 'Id_if2';
        const Dfastage_Else = 'Else';
        const Dfastage_Id_else1 = 'Id_else1';
        const Dfastage_Id_else2 = 'Id_else2';
        const Dfastage_Id_else3 = 'Id_else3';
        const Dfastage_Id_else4 = 'Id_else4';
        const Dfastage_Int = 'Int';
        const Dfastage_Id_int1 = 'Id_int1';
        const Dfastage_Id_int2 = 'Id_int2';
        const Dfastage_Id_int3 = 'Id_int3';
        const Dfastage_Id = 'Id';
        const Dfastage_GT = 'GT';
        const Dfastage_GE = 'GE';
        const Dfastage_Assignment = 'Assignment';
        const Dfastage_Plus = 'Plus';
        const Dfastage_Minus = 'Minus';
        const Dfastage_Star = 'Star';
        const Dfastage_Slash = 'Slash';
        const Dfastage_SemiColon = 'SemiColon';
        const Dfastage_LeftParen = 'LeftParen';
        const Dfastage_RightParen = 'RightParen';
        const Dfastage_IntLiteral = 'IntLiteral';

        //token的类型
        const TokenType_Plus = 'Plus'; // +
        const TokenType_Minus = 'Minus'; //-
        const TokenType_Star = 'Star'; // *
        const TokenType_Slash = 'Slash'; // /
        const TokenType_GE = 'GE';// >=
        const TokenType_GT = 'GT';// >
        const TokenType_EQ = 'EQ'; // ==
        const TokenType_LE = 'LE'; // <=
        const TokenType_LT = 'LT'; // <
        const TokenType_SemiColon = 'SemiColon';// ;
        const TokenType_LeftParen = 'LeftParen';// (
        const TokenType_RightParen = 'RightParen';// )
        const TokenType_Assignment = 'Assignment';// =
        const TokenType_If = 'If';
        const TokenType_Else = 'Else';
        const TokenType_Int = 'Int';
        const TokenType_Identifier = 'Identifier';//标识符
        const TokenType_IntLiteral = 'IntLiteral';//整形字面量
        const TokenType_StringLiteral = 'StringLiteral';//字符串字面量

        /**
         * 有限状态机进入初始状态
         * 这个初始状态其实并不做停留，它马上进入其他状态
         * 开始解析的时候，进入初始状态；
         * 某个Token解析完毕，也进入初始状态；
         * 在这里把token记下来
         *
         * @param [type] $ch
         * @return void
         */
        private function initToken($ch) {
            //如果此时tokenText不为空，说明是“某个Token解析完毕，也进入初始状态；”
            if (strlen($this->tokenText) > 0) {
                $this->token['text'] = $this->tokenText;
                $this->tokens[] = $this->token;
                //重新初始化
                $this->tokenText = '';
                $this->token = array();
            }

            $newState = self::Dfastage_Initial;
            if ($this->isAlpha($ch)) {
                if ($ch == 'i') {
                    $newState = self::Dfastage_Id_int1;
                } else {
                    $newState = self::Dfastage_Id;
                }
                $this->token['type'] = self::TokenType_Identifier;
                $this->tokenText = $this->tokenText.$ch;
            } elseif (is_numeric($ch)) {
                $newState = self::Dfastage_IntLiteral;
                $this->token['type'] = self::TokenType_IntLiteral;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '+') {
                $newState = self::Dfastage_Plus;
                $this->token['type'] = self::TokenType_Plus;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '-') {
                $newState = self::Dfastage_Minus;
                $this->token['type'] = self::TokenType_Minus;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '-') {
                $newState = self::Dfastage_Minus;
                $this->token['type'] = self::TokenType_Minus;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '*') {
                $newState = self::Dfastage_Star;
                $this->token['type'] = self::TokenType_Star;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '/') {
                $newState = self::Dfastage_Slash;
                $this->token['type'] = self::TokenType_Slash;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '>') {
                $newState = self::Dfastage_GT;
                $this->token['type'] = self::TokenType_GT;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '=') {
                $newState = self::Dfastage_Assignment;
                $this->token['type'] = self::TokenType_Assignment;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == '(') {
                $newState = self::Dfastage_LeftParen;
                $this->token['type'] = self::TokenType_LeftParen;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == ')') {
                $newState = self::Dfastage_RightParen;
                $this->token['type'] = self::TokenType_RightParen;
                $this->tokenText = $this->tokenText . $ch;
            } elseif ($ch == ';') {
                $newState = self::Dfastage_SemiColon;
                $this->token['type'] = self::TokenType_SemiColon;
                $this->tokenText = $this->tokenText . $ch;
            } else {
                $newState = self::Dfastage_Initial;
            }
            return $newState;
        }

        /**
         * 解析字符串，形成Token
         * 这是一个有限状态自动机，在不同的状态中迁移
         *
         * @param string $code
         * @return void
         */
        public function tokenize($code) {
            //初始化
            $this->tokens = array();
            $this->tokenText = '';
            $this->token = array();
            $state = self::Dfastage_Initial;
            //对字符串的每个字符进行遍历
            $codeStr = str_split($code);
            foreach ($codeStr as $ch) {
                // echo "state:{$state}\n";
                switch ($state) {
                    case self::Dfastage_Initial:
                        $state = $this->initToken($ch);
                        break;
                    case self::Dfastage_Id:
                        if ($this->isAlpha($ch) || is_numeric($ch)) {
                            $this->tokenText = $this->tokenText.$ch;
                        } else {
                            $state = $this->initToken($ch);
                        }
                        break;
                    case self::Dfastage_Id_int1:
                        if ($ch == 'n') {
                            $state = self::Dfastage_Id_int2;
                            $this->tokenText = $this->tokenText . $ch;
                        } else {
                            $state = self::Dfastage_Id;
                            $this->tokenText = $this->tokenText . $ch;
                        }
                        break;
                    case self::Dfastage_Id_int2:
                        if ($ch == 't') {
                            $state = self::Dfastage_Id_int3;
                            $this->tokenText = $this->tokenText . $ch;
                        } else {
                            $state = self::Dfastage_Id;
                            $this->tokenText = $this->tokenText . $ch;
                        }
                        break;
                    case self::Dfastage_Id_int3:
                        if ($this->isBlank($ch)) {
                            $state = self::Dfastage_Int;
                            $state = $this->initToken($ch);
                        } else {
                            $this->tokenText = $this->tokenText . $ch;
                            $state = self::Dfastage_Id;
                        }
                        break;
                    case self::Dfastage_GT:
                        if ($ch == '=') {
                            $this->token['type'] = self::TokenType_GE;
                            $state = self::Dfastage_GE;
                            $this->tokenText = $this->tokenText . $ch;
                        } else {
                            $state = $this->initToken($ch);
                        }
                        break;
                    case self::Dfastage_GE:
                    case self::Dfastage_Assignment:
                    case self::Dfastage_Plus:
                    case self::Dfastage_Minus:
                    case self::Dfastage_Star:
                    case self::Dfastage_Slash:
                    case self::Dfastage_SemiColon:
                    case self::Dfastage_LeftParen:
                    case self::Dfastage_RightParen:
                    case self::Dfastage_IntLiteral:
                        $state = $this->initToken($ch);
                        break;
                    default:
                        # code...
                        break;
                }
            }
            //把最后的一个token添加进去
            if (!empty($this->tokenText)) {
                $this->initToken($ch);
            }
        }

        public function print($str) {
            echo "{$str}\n";
            $this->tokenize($str);
            foreach ($this->tokens as $item) {
                $type = $item['type'];
                $text = $item['text'];
                echo "{$text}:{$type}\n";
            }
            echo "\n\n\n";
        }

        //是否为字母
        private function isAlpha($ch) {
            return preg_match('/[a-zA-Z]/', $ch);
        }

        //是否为空格
        private function isBlank($ch) {
            return $ch == ' ' || $ch == '\t' || $ch == '\n';
        }
    }

    $obj = new SimpleLexer();
    $obj->print("inta+intb*3");
    $obj->print("inta + intb * 3");
    $obj->print("5+8");
    $obj->print("int age = 45;");
    $obj->print("inta age = 45;");
    $obj->print("in age = 45;");
    $obj->print("age >= 45;");
    $obj->print("age > 45;");