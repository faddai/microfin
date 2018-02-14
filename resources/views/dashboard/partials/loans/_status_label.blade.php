<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/03/2017
 * Time: 08:49
 */
?>
<span class="label label-{{ $loan->getStatus() === 'Active' ? 'success' : 'danger' }}">{{ $loan->getStatus() }}</span>