<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 20:12
 */
?>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name') }}
            </a>
        </div>


        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{ url('/') }}"><i class="fa fa-home"></i> Home</a></li>
                <li class="dropdown">
                    <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
                        <i class="fa fa-users"></i> Clients <span class="caret"></span>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('clients.create') }}"><i class="fa fa-user-plus"></i> Add Client</a></li>
                            <li><a href="{{ route('clients.index') }}"><i class="fa fa-search"></i> Search for Client</a></li>
                            <li><a href="{{ route('client.transactions.index') }}"><i class="fa fa-list"></i> Transactions</a></li>
                        </ul>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fa fa-balance-scale"></i> Loans <span class="caret"></span>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('loans.create') }}"><i class="fa fa-plus"></i> New Loan</a></li>
                            <li><a href="{{ route('loans.index') }}"><i class="fa fa-list"></i> Loan Applications</a></li>
                            <li><a href="{{ route('loans.approved') }}"><i class="fa fa-list-alt"></i> Approved Loans</a></li>
                            <li><a href="{{ route('loans.disbursed') }}"><i class="fa fa-check-square-o"></i> Disbursed Loans</a></li>
                            <li><a href="{{ route('loans.restructured') }}"><i class="fa fa-refresh"></i> Restructured Loans</a></li>
                            <li><a href="{{ route('loans.payoff.index') }}"><i class="fa fa-check"></i> Loan Payoffs</a></li>
                            <li class="divider"></li>
                            <li><a href="#"><i class="fa fa-calculator"></i> What-if Analysis</a></li>
                            <li><a href="{{ route('reports.loans.index') }}"><i class="fa fa-line-chart"></i> Loan Reports</a></li>
                        </ul>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fa fa-money"></i> Accounting <span class="caret"></span>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{ route('accounting.transactions.create') }}">
                                    <i class="fa fa-plus"></i> Manual G.L. Entry
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('accounting.transactions.unapproved.index') }}">
                                    <i class="fa fa-check"></i> G.L. Entry Approval
                                </a>
                            </li>
                            <li><a href="#"><i class="fa fa-file"></i> G.L. Statement</a></li>
                            <li><a href="{{ route('accounting.income_statement') }}"><i class="fa fa-file-o"></i> Income Statement</a></li>
                            <li><a href="{{ route('accounting.trial_balance.index') }}"><i class="fa fa-th"></i> Trial Balance</a></li>
                            <li><a href="{{ route('accounting.balance_sheet') }}"><i class="fa fa-th-list"></i> Balance Sheet</a></li>
                            <li class="divider"></li>
                            <li><a href="{{ route('accounting.chart') }}"><i class="fa fa-bar-chart"></i> Chart of Accounts</a></li>
                            <li><a href="#"><i class="fa fa-sort-numeric-asc"></i> Budgets</a></li>
                        </ul>
                    </a>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        {{ $authUser->name }} ({{  $authUser->branch->name ?? 'Root' }}) <span class="caret"></span>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{ route('settings.index') }}">
                                    <i class="fa fa-gear"></i> Settings
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out"></i> Logout
                                </a>

                                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>

                        </ul>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
