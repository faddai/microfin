<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 25/04/2017
 * Time: 19:26
 */

namespace App\Contracts;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface ReportsInterface
{
    /**
     * @param Request $request
     */
    public function __construct(Request $request);

    /**
     * Add logic to retrieve data for this report
     *
     * @return Collection
     */
    public function handle(): Collection;

    /**
     * Returns the title of this report
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns the heading used to display report data in HTML table
     * or exported file formats (CSV, Excel, PDF)
     *
     * @return array
     */
    public function getHeader(): array;
}