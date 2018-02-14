<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/12/2016
 * Time: 10:57
 */
?>
<div class="col-md-12">
    <div class="col-md-2 col-sm-6 col-sm-offset-3 col-md-offset-5 m-b-lg">
        <img src="{{ $client->getProfilePhoto()  }}" class="img-circle" width="150px"
             alt="{{ $client->getFullname() ?: 'N/A' }}">

        <div class="text-center m-t-md">
            <small class="text-info">Account balance</small>
            <p class="lead m-b-n"><small>{{ $currency }}</small> {{ $client->getAccountBalance() }}</p>
        </div>
    </div>
</div>

