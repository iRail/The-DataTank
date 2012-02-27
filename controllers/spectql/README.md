The grammar of this file should look something like the HTSQL specification:


    Here is the grammar of HTSQL::

        input           ::= segment END

        segment         ::= '/' ( top command* )?
        command         ::= '/' ':' identifier ( '/' top? | call | flow )?

        top             ::= flow ( direction | mapping )*
        direction       ::= '+' | '-'
        mapping         ::= ':' identifier ( flow | call )?

        flow            ::= disjunction ( sieve | quotient | selection )*
        sieve           ::= '?' disjunction
        quotient        ::= '^' disjunction
        selection       ::= selector ( '.' atom )*

        disjunction     ::= conjunction ( '|' conjunction )*
        conjunction     ::= negation ( '&' negation )*
        negation        ::= '!' negation | comparison

        comparison      ::= expression ( ( '~' | '!~' |
                                           '<=' | '<' | '>=' |  '>' |
                                           '==' | '=' | '!==' | '!=' )
                                         expression )?

        expression      ::= term ( ( '+' | '-' ) term )*
        term            ::= factor ( ( '*' | '/' ) factor )*
        factor          ::= ( '+' | '-' ) factor | pointer

        pointer         ::= specifier ( link | assignment )?
        link            ::= '->' flow
        assignment      ::= ':=' top

        specifier       ::= atom ( '.' atom )*
        atom            ::= '@' atom | '*' index? | '^' | selector | group |
                            identifier call? | reference | literal
        index           ::= NUMBER | '(' NUMBER ')'

        group           ::= '(' top ')'
        call            ::= '(' arguments? ')'
        selector        ::= '{' arguments? '}'
        arguments       ::= argument ( ',' argument )* ','?
        argument        ::= segment | top
        reference       ::= '$' identifier

        identifier      ::= NAME
        literal         ::= STRING | NUMBER